<?php

use Adianti\Database\TFilter1;
use Adianti\Widget\Datagrid\TDatagridTables;

class LocaisList extends TPage
{

    private $form;
    private $datagrid;

    public function __construct()
    {

        parent::__construct();

        $this->form = new BootstrapFormBuilder('list_locais');
		$this->form->setFormTitle( "Listagem de Locais" );
		$this->form->class = "tform";

        $opcao = new TCombo( "opcao" );
        $dados = new TEntry( "dados" );

        $opcao->setDefaultOption( "..::SELECIONE::.." );
        $dados->setProperty( "title", "Informe os dados de acordo com a opção" );

        $opcao->setSize( "38%" );
        $dados->setSize( "38%" );

        $items = array();
        $items['nome'] = 'Nome';
		$items['municipio'] = 'Município';

        $opcao->addItems($items);

		$this->form->addFields( [ new TLabel( "Opção de busca:" ) ], [ $opcao ] );
        $this->form->addFields( [ new TLabel( "Dados à buscar:" )  ], [ $dados ] );
		
		$this->form->addAction( "Buscar", new TAction( [ $this, "onSearch" ] ), "fa:search" );
        $this->form->addAction( "Novo", new TAction( [ "LocaisForm", "onEdit" ] ), "bs:plus-sign green" );

		$this->datagrid = new BootstrapDatagridWrapper( new TDataGrid() );
        $this->datagrid->datatable = "true";
        $this->datagrid->style = "width: 100%";
        $this->datagrid->setHeight( 320 );

        $dgnome = new TDataGridColumn('nome', 'Nome', 'left', 200);
        $dgmunicipio = new TDataGridColumn('municipio', 'Município', 'left', 200);
        $dglatitude = new TDataGridColumn('latitude', 'Latitude', 'left', 200);
        $dglongitude = new TDataGridColumn('longitude', 'Longitude', 'left', 200);

        $this->datagrid->addColumn($dgnome);
        $this->datagrid->addColumn($dgmunicipio);
        $this->datagrid->addColumn($dglatitude);
        $this->datagrid->addColumn($dglongitude);

        $actionEdit = new TDataGridAction(array('LocaisForm', 'onEdit'));
        $actionEdit->setLabel('Editar');
        $actionEdit->setImage('ico_edit.png');
        $actionEdit->setField('id');

        $actionDelete = new TDataGridAction(array($this, 'onDelete'));
        $actionDelete->setLabel('Deletar');
        $actionDelete->setImage('ico_delete.png');
        $actionDelete->setField('id');

        $this->datagrid->addAction($actionEdit);
        $this->datagrid->addAction($actionDelete);

        $this->datagrid->createModel();

        $this->pageNavigation = new TPageNavigation();
        $this->pageNavigation->setAction( new TAction( [ $this, "onReload" ] ) );
        $this->pageNavigation->setWidth( $this->datagrid->getWidth() );

        $container = new TVBox();
        $container->style = "width: 90%";
        $container->add( $this->form );
        $container->add( TPanelGroup::pack( NULL, $this->datagrid ) );
        $container->add( $this->pageNavigation );

        parent::add( $container );
    }

    function onReload()
    {
        TTransaction::open('database');

        $repository = new TRepository('LocaisRecord');
        $criteria = new TCriteria;

        $cadastros = $repository->load($criteria);

        $this->datagrid->clear();

        if ($cadastros) {
            foreach ($cadastros as $cadastro) {
                $this->datagrid->addItem($cadastro);
            }
        }

        TTransaction::close();

        $this->loaded = true;
    }

    function onSearch()
    {
        $data = $this->form->getData();

        $campo = $data->opcao;
        $dados = $data->nome;

        TTransaction::open('database');

        $repository = new TRepository('LocaisRecord');
        $criteria = new TCriteria;

        if ($dados) {
            if (is_numeric($dados)) {
                $criteria->add(new TFilter($campo, 'like', '%' . $dados . '%'));
            } else {
                $criteria->add(new TFilter1('special_like(' . $campo . ",'" . $dados . "')"));
            }
        }

        $objects = $repository->load($criteria);

        $this->datagrid->clear();
        if ($objects) {
            foreach ($objects as $object) {
                $this->datagrid->addItem($object);
            }
        }

        TTransaction::close();

        $this->loaded = true;
    }

    function onDelete($param)
    {
        $key = $param['key'];
        $action = new TAction(array($this, 'Delete'));
        $action->setParameter('key', $key);
		
        new TQuestion('Deseja realmente excluir o registro?', $action);
    }


    function Delete($param)
    {
        $key = $param['key'];

        TTransaction::open('database');

        $obj = new LocaisRecord($key);

        try {
            $obj->delete();

            new TMessage("info", "Registro deletado com sucesso!");

            TTransaction::close();

        } catch (Exception $e) {
            new TMessage('error', $e->getMessage());
            TTransaction::rollback();
        }

        $this->onReload();
    }

    function show()
    {
        $this->onReload();
        parent::show();

    }
}
