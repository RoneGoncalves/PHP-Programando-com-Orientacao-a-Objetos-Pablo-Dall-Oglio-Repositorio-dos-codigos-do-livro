<?php
/**
 * classe TWindow
 * TWindow é um container que exibe seu conteudo em uma camada simulando uma janela
 */
class TWindow
{
    private $x;              // coluna 
    private $y;              // linha
    private $width;          // largura
    private $height;         // altura
    private $title;          // título da janela
    private $content;        // conteúdo da janela
    static private $counter; // contador


    /**
     * método construto
     * incrementa o contador de janelas
     */
    public function __construct($title)
    {
        // incrementa o contador de janelas para exibir cada um com um ID difernte
        self::$counter ++;
        $this->title = $title;
    }

    /**
     * método setPosition()
     * difine a cluna e linha (x,y) que a janela será exibida na tela
     *  @param $x = coluna (em pixels)
     *  @param $y = linha  (em pixels) 
     */
    public function setPosition($x, $y)
    {
        // atrubui os pontos do canto superios esquerdo da janela
        $this->x = $x;
        $this->y = $y;
    }

    /**
     * método setSize()
     * define a largura e altura da janela em tela
     *  @param $width = largura (em pixels)
     *  @param $height = altura (em pixels)
     */
    public function setSize($width, $height)
    {
        // atribui as dimensões
        $this->width  = $width;
        $this->height = $height;
    }

    /**
     * método add()
     * adiciona um conteúdo à janela
     *  @param $content = conteúdo a ser adicionado
     */
    public function add($content)
    {
        $this->content = $content;
    }

    /**
     * método show()
     * exibe a janela na tela
     */
    public function show()
    {
        $window_id = 'TWindow'.self::$counter;

        // instancia objeto TStyle para definir as características
        // de posicionamento e dimensão da camada criada
        $style = new TStyle($window_id);
        $style->position     = 'absolute';
        $style->left         = $this->x;
        $style->top          = $this->y;
        $style->width        = $this->width;
        $style->height       = $this->height;
        $style->border       = '1px solid #000000';
        $style->border_color = 'grey';
        $style->background   = '#e0e0e0';
        $style->z_index      = 10000;

        // exibe o estilo em tela
        $style->show();

        // cria tag <div> para a camada que representará a janela
        $painel = new TElement('div');
        $painel->id = $window_id;    // define o ID
        $painel->class = $window_id; // defina a classe CSS

        // instancia objeto TTable
        $table = new TTable;

        // define as propriedades da tabela
        $table->width  = '100%';
        $table->height = '100%';
        $table->style  = 'border-collapse:collapse';

        // adiciona uma linha para o título
        $row_1 = $table->addRow();
        $row_1->bgcolor = '#707070';
        $row_1->height  = '20px';

        // adiciona uma célula para o título
        $titulo = $row_1->addCell("<font face=Arial size=2 color=White><b>{$this->title}<lb><lfont>");
        $titulo->width = '100%';

        // instacia uma imagem
        $img = new TImage('../app.images/ico_close.png');
        $img->width = 20;
        $img->height = 20;

        // cria um link com ação para esconder o <div>
        $link = new TElement('a');
        $link->add($img);
        $link->onclick = "document.getElementById('$window_id').style.display='none'";

        // adicioa uma célula com link de fechar
        $cell = $row_1->addCell($link);

        // cria uma linha para o conteúdo
        $row_2 = $table->addRow();
        $row_2->valign = 'top';

        // adiciona o conteúdo ocupando duas colunas (colspan)
        $cell = $row_2->addCell($this->content);
        $cell->colspan = 2;

        // adiciona a tabela ao painel
        $painel->add($table);

        // exibe o painel
        $painel->show();
    }
} 