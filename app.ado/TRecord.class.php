<?php
/**
 * Classe TRecord
 * Esta classe provê os métodos necessários para persistir e recuperar 
 * objetos da base de dados (Active Record) 
 */
abstract class TRecord
{
    protected $data;  // array contendo os dados do objeto
   /**
    * método __construct()
    * instancia um Active Record. Se passado o $id, ja carrega o objeto
    *  @param [$id] = ID do objeto
    */ 
    public function __construct($id = NULL)
    {
        if ($id) // se o ID for informado
        {
            // carrega o objeto correspondente
            $object = $this->load($id);

            if($object)
            {
                $this->fromArray($object->toArray());
            }
        }
    }

    /**
     * método __clone()
     * executado quando o objeto for clonado
     * limpa o ID para que seja gerado um novo ID para o clone
     */
    public function __clone()
    {
        unset($this->id);
    }

    /**
     * método __set()
     * executado sempre que uma propriedade for atribuida
     */
    public function __set($prop, $value)
    {
        // Verifia se existe método set_<proprety>
        if(method_exists($this, 'set_'.$prop))
        {
            //executa o método set_<proprety>
            call_user_func(array($this, 'set_'.$prop), $value);
        }
        else
        {
            // atribui o valor da propriedade
            //$this->data[$prop] = $value;

            if ($value === NULL)
            {
                unset($this->data[$prop]);
            }
            else
            {
                // atribui o valor da propriedade
                $this->data[$prop] = $value;
            }
        }
    }

    /**
     * MÉTODO __get($prop)
     * executado sempre que uma propriedade for requerida
     */
    public function __get($prop)
    {
        // verifica se existe método get_<property>
        if(method_exists($this, 'get_'.$prop))
        {
            // executa o método get_<property>
            return call_user_func(array($this, 'get_'.$prop));
        }
        else
        {
            // Correção
            // Checando se "data[$prop]" está setado para evitar 
            // Notice: Trying to access array offset on value of type null 
            if(isset($this->data[$prop]))
            {
                return $this->data[$prop];
            }
        }
    }

    /**
     * método getEntity()
     * retorna o nome da entidade (tabela)
     */
    private function getEntity()
    {
        // obtém o nome da classe
        $class = strtolower(get_class($this));
        // retorna o nome da classe - "Record"
        return substr($class, 0, -6);
    }

    /**
     * método fromArray()
     * preeche os dados do objeto com um array
     */
    public function fromArray($data)
    {
        $this->data = $data;
    }

    /**
     * método toArray
     * retorna os dados do objeto como array
     */
    public function toArray()
    {
        return $this->data;
    }

    /**
     * método store()
     * armazena o objeto na base de dados e retorna o número de
     * linha afetadas pela instrução SQL (zero ou um)
     */
    public function store()
    {
        // verifica se ten ID ou se existe na base de dados
        if(empty($this->data['id']) or (!$this->load($this->id)))
        {
            // incrementa o ID
            $this->id = $this->getLast() +1;

            // cria uma instrução de insert
            $sql = new TSqlInsert;
            $sql->setEntity($this->getEntity());


            // percorre os dados do objeto sql
            foreach($this->data as $key => $value)
            {
                // passa os dados do objeto para o SQL
                $sql->setRowData($key, $this->$key);
                //$sql->setRowData($key, $this->key);
            }
        }
        else
        {
            // instancia instrução de update
            $sql = new TSqlUpdate;
            $sql->setEntity($this->getEntity());
            
            // cria um critério de seleção baseado no ID
            $criteria = new TCriteria;
            $criteria->add(new TFilter('id', '=', $this->id));
            $sql->setCriteria($criteria);

            // percorre os dados do objeto
            foreach($this->data as $key => $value)
            {
                if($key !== 'id')  // o ID não precisa ir no UPDATE
                {
                    // passa os dados do objeto para o SQL
                    $sql->setRowData($key, $this->$key);
                    //$sql->setRowData($key, $this->key);
                }
            }
        }

        // obtem a transação ativa
        if($conn = TTransaction::get())
        {
            // faz o log e executa o SQL
            TTransaction::log($sql->getInstruction());
            $result = $conn->exec($sql->getInstruction());

            // retorna o resultado
            return $result;
        }
        else
        {
            // se não tiver transação, retorna uma exceção
            throw new Exception('Não há transação ativa!!');
        }
    }

    /**
     * método load()
     * recupera (retorna) um objeto da base de dados
     * através de seu ID e instancia ele na memória
     *  @param $id = ID do objeto 
     */
    public function load($id)
    {
        // instancia instrução SELECT
        $sql = new TSqlSelect;
        $sql->setEntity($this->getEntity());
        $sql->addColumn('*');

        // cria critério para seleção baseado no ID
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', '=', $id));

        // difine o critério deseleção de dados
        $sql->setCriteria($criteria);

        // obtem transação ativa
        if($conn = TTransaction::get())
        {
            // cria mensagem de log e executa a consulta
            TTransaction::log($sql->getInstruction());
            $result = $conn->Query($sql->getInstruction());

            // se retornou algum dado
            if($result)
            {
                // retorna os dados em forma de objeto
                $object = $result->fetchObject(get_class($this));
            }

            return $object;
        }
        else
        {
            // se não tiver transação, retorna uma exceção 
            throw new Exception('Não há transação ativa!!');
        }
    }

    /**
     * método delete()
     * exclui um objeto da base de dados através de seu ID.
     *  @param $id = ID do objeto
     */
    public function delete($id = NULL)
    {
        // O ID é o parâmetro ou a propriedade
        $id = $id ? $id: $this->id;

        // instancia uma instrução DELETE
        $sql = new TSqlDelete;
        $sql->setEntity($this->getEntity());


        //cria  critério de seleção
        $criteria = new TCriteria;
        $criteria->add(new TFilter('id', '=', $id)); 

        // define o critério de seleção baseado no ID
        $sql->setCriteria($criteria);

        // Obtém transação ativa
        if($conn = TTransaction::get())
        {
            // faz o log e executa o SQL
            TTransaction::log($sql->getInstruction());
            $result = $conn->exec($sql->getInstruction());

            // retorna o resultado
            return $result;
        }
        else
        {
            // se não houver transação ativa, retorna uma exceção
            throw new Exception('Não existe transação ativa!!');
        }
    }

    /**
     * métod getLast()
     * retorna o último ID
     */
    private function getLast()
    {
        // inicia transação
        if($conn = TTransaction::get())
        {
            $sql = new TSqlSelect;
            $sql->addColumn('max(ID) as ID');
            $sql->setEntity($this->getEntity());

            // cria log e executa a instrução SQL
            TTransaction::log($sql->getInstruction());
            $result = $conn->Query($sql->getInstruction());

            // retorna os dados do banco
            $row = $result->fetch();
            return $row[0];
        }
        else
        {
            // se não houver transação ativa, retorna uma exceção
            throw new Exception('Não existe transação ativa!!');
        }
    }
}