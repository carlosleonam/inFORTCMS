<?php
/**
 * ArquivosList
 *
 * @version     1.0
 * @package     control
 * @subpackage  arquivos
 * @author      André Ricardo Fort
 * @copyright   Copyright (c) 2020 (https://www.infort.eti.br)
 */
class ArquivosList extends TPage
{
    private $iconview;
    private $form;
    private $loaded;
    
    /**
     * Constructor method
     */
    public function __construct()
    {
        parent::__construct();
        
        // criando formulário de filtragem
        $this->form = new BootstrapFormBuilder('form_search_ArquivosList');
        $this->form->setFormTitle('Gestão de Arquivos');
        $this->form->setFieldSizes('100%');
        
        // criando os campos do formulário
        $arquivo = new TEntry('arquivo');

        // adicionando os campos
        $this->form->addFields( [ new TLabel('Arquivo') ], [ $arquivo ] );
        
        // keep the form filled during navigation with session data
        $this->form->setData( TSession::getValue(__CLASS__.'_filter_data') );
        
        // adicionando botões de ação
        $this->addActionButton(_t('Find'), new TAction(array($this, 'onSearch')), 'fa:search','btn-primary');
        $this->addActionButton(_t('Clear'), new TAction(array($this, 'onClear')), 'fa:eraser red');
        $this->addActionButton('Criar Pasta', new TAction(array($this, 'onCreateFolder')), 'fas:folder-plus white', 'btn-warning white');
        $this->addActionButton(_t('Upload'), new TAction(array($this, 'onUpload')), 'fas:cloud-upload-alt', 'btn-success');
        
        // criando campo de ícones
        $this->iconview = new TIconView;
        
        $opendir = '..' . CMS_IMAGE_PATH;
        if (!empty(TSession::getValue(__CLASS__.'_opendir')))
            $opendir = TSession::getValue(__CLASS__.'_opendir');
        else
            TSession::setValue(__CLASS__.'_opendir',$opendir);
            
        // vertical box container
        $container = new TVBox;
        $container->style = 'width: 100%';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        $container->add(TPanelGroup::pack(new TImage('fa:folder') . ' : ' . $opendir, $this->iconview));
        
        parent::add($container);
    }
    
    /**
     * Cria formulário para upload de arquivo
     */
    public function onUpload($param)
    {
        $form = new BootstrapFormBuilder('input_file_form');
        $form->setFieldSizes('100%');
        
        $file = new TSlim('file');
        
        $form->addFields( [ $file] );
        
        $file->setDataProperties(['label'=>'Upload de imagem']);//aqui eu seto o nome do label
        //tamanho final no máximo 1500x1500 e proporção de 4:3 na janela de visualização
        $file->setDataProperties(['size'=>'1200,1200','ratio'=>'16:9','download'=>'true']);
        //$file->setWatermark(THelper::getPreferences('pref_site_nome'));
        //$file->setImageWatermark('app/images/logo-infort.svg');
        
        $btn = $form->addAction(_t('Save'), new TAction([__CLASS__, 'Upload']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        $form->addAction(_t('Cancel'), new TAction([__CLASS__, 'onReload']), 'fa:times red');
        
        // show the input dialog
        new TInputDialog('Upload de Arquivo', $form);
    }
    
    /**
     * Faz o upload do arquivo
     */
    public static function Upload( $param )
    {
        try
        {
            $images = Slim::getImages();
            
            // No image found under the supplied input name
            if ($images)
            {            
                $image = $images[0];
                // save output data if set
                if (isset($image['output']['data']))
                {
                    $arquivo = pathinfo($image['output']['name']);
                    
                    // geramos um hash com o nome do arquivo concatenado com o tempo
                    //$name = time().'-'.md5($arquivo['filename']).'.'.$arquivo['extension'];
                    $name = THelper::urlAmigavel($arquivo['filename']).'.'.$arquivo['extension'];
                    
                    // We'll use the output crop data
                    $output_data = $image['output']['data'];
                    
                    // definindo o path com a categoria pai'
                    $target_path = TSession::getValue(__CLASS__.'_opendir') . DIRECTORY_SEPARATOR;
                    
                    // salva o arquivo
                    $output = Slim::saveFile($output_data, $name, $target_path, false);
                    
                    if ($output)
                    {
                        if( file_exists ($output['path']) )// se existir apaga o anterior
                        {
                            // pegamos o arquivo salvo e convertemos para webp
                            $webp_file = str_replace('.'.$arquivo['extension'],'.webp',$output['path']);
                            THelper::toWebP($output['path'],$webp_file);
                            
                            // apagando o arquivo original
                            unlink( $output['path'] ); //apaga
                        }
                    }
                    
                }
            }

            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction([__CLASS__,'onReload']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    /**
     * Cria formulário para criação da pasta
     */
    public static function onCreateFolder($param)
    {
        $form = new BootstrapFormBuilder('input_folder_form');
        $form->setFieldSizes('100%');
        
        $nome = new TEntry('nome');
        
        $form->addFields( [ new TLabel('Nome da Pasta') , $nome] );
        
        $btn = $form->addAction(_t('Create'), new TAction([__CLASS__, 'newFolder']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        $form->addAction(_t('Cancel'), new TAction([__CLASS__, 'onReload']), 'fa:times red');
        
        // show the input dialog
        new TInputDialog('Nova Pasta', $form);
    }
    
    /**
     * Salva a nova pasta no diretório atual
     */
    public static function newFolder( $param )
    {
        try
        {
            $nome = THelper::urlAmigavel( $param['nome'] );

            // validando os campos
            if ( empty($nome) )
            {
                throw new Exception('Ouve um erro ao tentar criar a pasta!');
            }
            
            mkdir(TSession::getValue(__CLASS__.'_opendir') . DIRECTORY_SEPARATOR . $nome, 0755);

            new TMessage('info', TAdiantiCoreTranslator::translate('Record saved'), new TAction([__CLASS__,'onReload']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    
    
    
    /**
     * Open action
     */
    public static function onOpen($param)
    {
        if (is_dir( $param['path'] . DIRECTORY_SEPARATOR . $param['name']) || $param['name'] == 'Voltar') 
        {
            $dir = ($param['name'] == 'Voltar') ? $param['path'] : $param['path'] . DIRECTORY_SEPARATOR . $param['name'];
            TSession::setValue(__CLASS__.'_opendir',$dir);
            TApplication::loadPage(__CLASS__);
        }
        else 
        {
            unset($param['static']);
            $param['register_state'] = 'false';
            TApplication::loadPage('ArquivosFormView','onView',$param);
            //AdiantiCoreApplication::loadPage('ArquivosFormView','onView',$param);
        }
    }
    
    
    /**
     * Cria formulário para renomear um arquivo ou pasta
     */
    public static function onRename($param)
    {
        $form = new BootstrapFormBuilder('rename_form');
        $form->setFieldSizes('100%');
        
        $atual = new TEntry('atual');
        $novo  = new TEntry('novo');
        //$type  = new THidden('type');
        
        $form->addFields( [ new TLabel('Nome atual') , $atual ] );
        $form->addFields( [ new TLabel('Novo nome') , $novo ] );
        //$form->addFields( [ $type ] );
        
        $atual->setEditable(false);
        $atual->setValue($param['name']);
        //$type->setValue($param['type']);
        
        $btn = $form->addAction(_t('Save'), new TAction([__CLASS__, 'Rename']), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        $form->addAction(_t('Cancel'), new TAction([__CLASS__, 'onReload']), 'fa:times red');
        
        // show the input dialog
        new TInputDialog('Renomear Arquivo/Pasta', $form);
    }
    
    /**
     * Renomeia o arquivo
     */
    public static function Rename( $param )
    {
        try
        {
            // validando os campos
            if ( empty($param['novo']) || empty($param['atual']) )
            {
                throw new Exception('Ouve um erro ao tentar criar a pasta!');
            }
            
            $arquivo = pathinfo($param['novo']);
            $atual   = pathinfo($param['atual']);

            // preparando o novo nome e garantindo a mesma extenção
            $novo = THelper::urlAmigavel( $arquivo['filename'] ) . (isset($atual['extension']) ? ('.' . $atual['extension']) : '');

            $dir = TSession::getValue(__CLASS__.'_opendir') . DIRECTORY_SEPARATOR;
            
            rename($dir.$param['atual'], $dir.$novo);
        
            new TMessage('info', _t('File saved'), new TAction([__CLASS__,'onReload']));
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    
    /**
     * Delete action
     */
    public static function onDelete($param)
    {
        // define the delete action
        $action = new TAction(array(__CLASS__, 'Delete'));
        $action->setParameters($param); // pass the key parameter ahead
        
        if (is_file($param['path'].'/'.$param['name']))
        {
            // shows a dialog to the user
            new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?'), $action);
        }
        else
        {
            new TQuestion(AdiantiCoreTranslator::translate('Do you really want to delete ?').'<br>Esta ação apagará todos os arquivos contidos nessa pasta.', $action);
        }
    }
    
    /**
     * Deleta o arquivo
     */
    public static function Delete($param)
    {
        try
        {
            $caminho = $param['path'].'/'.$param['name'];
            
            if (is_file($caminho))
            {
                unlink($caminho);
                
                new TMessage('info', _t('File deleted'), new TAction([__CLASS__,'onReload'])); // success message
            }
            else
            {
                THelper::apagarTudo($caminho); // apagando todo o conteúdo da pasta
                rmdir($caminho); // apagando a pasta
                
                new TMessage('info', _t('Folder deleted'), new TAction([__CLASS__,'onReload'])); // success message
            }
        }
        catch (Exception $e) // in case of exception
        {
            new TMessage('error', $e->getMessage()); // shows the exception error message
        }
    }
    
    
    /**
     * Método addActionButton para adicionar um botão com waves-effect
     * @param    $label    text content
     * @param    $action   TAction Object
     * @param    $icon     text icon (fa:user)
     * @param    $class    text class
     */
    private function addActionButton($label, TAction $action, $icon = null, $class = 'btn-default')
    {
        $btn = $this->form->addAction($label, $action, $icon);
        $btn->class = 'btn btn-sm '.$class.' waves-effect';

        return $btn;
    }
    
    /**
     * Register the filter in the session
     */
    public function onSearch($param)
    {
        // get the search form data
        $data = $this->form->getData();
        
        // clear session filters
        TSession::setValue(__CLASS__.'_filter_arquivo',  NULL);

        if (isset($data->arquivo) AND ($data->arquivo)) {
            TSession::setValue(__CLASS__.'_filter_arquivo',  $data->arquivo); // stores the filter in the session
        }
        
        // fill the form with data again
        $this->form->setData($data);
        
        // keep the search data in the session
        TSession::setValue(__CLASS__ . '_filter_data', $data);
        
        $this->onReload($param);
    }
    
    /**
     * Carregamos a listagem
     */
    public function onReload($param = NULL)
    {
        $filter = [];

        if (TSession::getValue(__CLASS__.'_filter_arquivo')) {
            $filter[] = TSession::getValue(__CLASS__.'_filter_arquivo'); // add the session filter
        }
        
        //***
        
        $opendir = '..' . CMS_IMAGE_PATH;

        if (!empty(TSession::getValue(__CLASS__.'_opendir')))
        {
            $opendir = TSession::getValue(__CLASS__.'_opendir');
        }
        
        $dir = new DirectoryIterator( $opendir );
        $arr = [];
        
        foreach ($dir as $fileinfo)
        {
            
            if (!$fileinfo->isDot())
            {
                $item = new stdClass;
                $n = 'd';
                if ($fileinfo->isDir())
                {
                    $item->type = 'folder';
                    $item->icon = 'fas:folder blue fa-4x';
                    $item->path = $fileinfo->getPath();
                    $item->name = $fileinfo->getFilename();
                    
                    $arr[$n.$fileinfo->getFilename()] = $item;
                }
                else
                {
                    $nome = $fileinfo->getFilename();
                    if ( str_replace($filter, '', $nome) != $nome || empty($filter) && !in_array($nome,['.htaccess']) )
                    {
                        $item->type = 'file';
                        $item->icon = $fileinfo->getPath().'/'.$fileinfo->getFilename(); //'far:file orange fa-4x';
                        $n = 'f';
                        $item->path = $fileinfo->getPath();
                        $item->name = $fileinfo->getFilename();
                        
                        $arr[$n.$fileinfo->getFilename()] = $item;
                    }
                }
                
            }

        }
        
        $arr_dir = explode('/',$opendir);
        
        if ( count($arr_dir) > 2 )
        {
            array_pop($arr_dir);
            
            $item = new stdClass;
            $item->type = 'folder';
            $item->icon = 'far:arrow-alt-circle-up dark fa-4x';
            $item->path = implode('/',$arr_dir);
            $item->name = 'Voltar';
            
            $arr['d'.$fileinfo->getFilename()] = $item;
        }
        
        ksort($arr);
        
        // iterando os objetos
        foreach ($arr as $item)
        {
            $this->iconview->addItem($item);
        }
        
        //$this->iconview->enablePopover('', '<b>Name:</b> {name}');
        
        $this->iconview->setIconAttribute('icon');
        $this->iconview->setLabelAttribute('name');
        $this->iconview->setInfoAttributes(['name', 'path']);
        
        $display_condition = function($object) {
            return ($object->type == 'file');
        };
        
        $this->iconview->addContextMenuOption('Ações');
        $this->iconview->addContextMenuOption('');
        $this->iconview->addContextMenuOption(_t('Open'),   new TAction([$this, 'onOpen']),   'far:folder-open blue');
        $this->iconview->addContextMenuOption(_t('Rename'), new TAction([$this, 'onRename']), 'far:edit green');
        $this->iconview->addContextMenuOption(_t('Delete'), new TAction([$this, 'onDelete']), 'far:trash-alt red'); //, $display_condition);
        
        //***
        
        $this->loaded = true;
    }
    
    /**
     * method show()
     * Shows the page
     */
    public function show()
    {
        // check if the datagrid is already loaded
        if (!$this->loaded AND (!isset($_GET['method']) OR !(in_array($_GET['method'],  array('onReload', 'onSearch')))) )
        {
            if (func_num_args() > 0)
            {
                $this->onReload( func_get_arg(0) );
            }
            else
            {
                $this->onReload();
            }
        }
        parent::show();
    }
    
    /**
     * Método para limpar os campos da pesquisa
     */
    public function onClear()
    {
        // limpando dados da sessão
        THelper::clearSession();

        $this->form->clear();
        TApplication::loadPage(__CLASS__);
    }
    
}