<?php
/**
 * PreferenciasForm
 *
 * @version    1.0
 * @package    control
 * @subpackage configuracoes
 * @author     André Ricardo Fort
 * @copyright  Copyright (c) 2019 inFORT (https://www.infort.eti.br)
 *
 */
class PreferenciasForm extends TStandardForm
{
    protected $form; // formulário
    
    /**
     * método construtor
     * Cria a página e o formulário de cadastro
     */
    public function __construct()
    {
        parent::__construct();
        
        $this->setDatabase('permission');
        $this->setActiveRecord('SystemPreference');
        
        // cria o formulário
        $this->form = new BootstrapFormBuilder('form_Preferencias');
        $this->form->setFormTitle('Preferencias Globais');
        $this->form->setFieldSizes('100%');
        
        // Mudando a cor do cabeçalho
        //$this->form->setProperty('class','card');
        $this->form->setHeaderProperty('class','header bg-red');
        
        // cria os campos do formulário
        // dados do site
        $pref_site_nome       = new TEntry('pref_site_nome');
        $pref_site_dominio    = new TEntry('pref_site_dominio');
        $pref_site_keywords   = new TMultiEntry('pref_site_keywords');
        $pref_site_descricao  = new TText('pref_site_descricao');
        $pref_site_language   = new TEntry('pref_site_language');
        $pref_site_manutencao = new TRadioGroup('pref_site_manutencao');
        $pref_site_mensagem   = new TEntry('pref_site_mensagem');
        $pref_cache_control   = new TRadioGroup('cache_control');
        // dados da empresa
        $pref_emp_nome        = new TEntry('pref_emp_nome');
        $pref_emp_email       = new TEntry('pref_emp_email');
        $pref_emp_fone        = new TEntry('pref_emp_fone'); // telefone
        $pref_emp_celular     = new TEntry('pref_emp_celular'); // celular
        $pref_emp_endereco    = new TEntry('pref_emp_endereco'); // endereço
        $pref_emp_cidade      = new TEntry('pref_emp_cidade'); // cidade
        $pref_emp_estado      = new TEntry('pref_emp_estado'); // estado
        $pref_emp_postal      = new TEntry('pref_emp_postal'); // código postal
        $pref_emp_pais        = new TEntry('pref_emp_pais'); // pais
        $pref_emp_cnpj        = new TEntry('pref_emp_cnpj');
        $pref_emp_maps        = new TTextSourceCode('pref_emp_maps'); // iframe do google maps
        // dados do Blog
        $pref_blog_titulo     = new TEntry('pref_blog_titulo');
        $pref_blog_subtitulo  = new TEntry('pref_blog_subtitulo');
        $pref_blog_metadesc   = new TText('pref_blog_metadesc');
        $pref_blog_metakey    = new TMultiEntry('pref_blog_metakey');
        // configurações de email
        $pref_mail_domain     = new TEntry('pref_mail_domain');
        $pref_smtp_auth       = new TCombo('pref_smtp_auth');
        $pref_smtp_host       = new TEntry('pref_smtp_host');
        $pref_smtp_port       = new TEntry('pref_smtp_port');
        $pref_smtp_user       = new TEntry('pref_smtp_user');
        $pref_smtp_pass       = new TPassword('pref_smtp_pass');
        $pref_mail_from       = new TEntry('pref_mail_from');
        $pref_mail_to         = new TEntry('pref_mail_to');
        // apis sociais
        $pref_instagram_token = new TEntry('pref_instagram_token');
        $pref_instagram_userid = new TEntry('pref_instagram_userid');
        
        /*
        // dados modulares (geo, blog, etc)
        $mod_geo              = new TRadioGroup('mod_geo');
        $mod_blog             = new TRadioGroup('mod_blog');
        */
        
        // parametros dos campos
        //$pref_emp_maps->id = 'html_mapa';
        $pref_smtp_host->placeholder = 'ssl://smtp.gmail.com, tls://server.company.com';
        $pref_emp_cnpj->setMask('99.999.999/9999-99');
        //$pref_emp_fone->setMask('(99) 9999.9999');
        $pref_emp_celular->setMask('(99) 99999.9999');
        
        $yesno = array();
        $yesno['1'] = _t('Yes');
        $yesno['0'] = _t('No');
        $pref_smtp_auth->addItems($yesno);
        $pref_site_manutencao->addItems($yesno);
        $pref_site_manutencao->setLayout('horizontal');
        //$pref_site_manutencao->setUseButton();
        $pref_site_manutencao->setChangeAction(new TAction([$this,'onChangeManutencao']));
        $pref_cache_control->addItems($yesno);
        $pref_cache_control->setLayout('horizontal');
        //$pref_cache_control->setUseButton();
        $pref_site_mensagem->setEditable(false);
        $pref_site_mensagem->setValue('Este site está em manutenção.');
        $pref_site_language->placeholder = 'Ex: pt-br, en-us, es';
        
        $pref_site_dominio->forceLowerCase();
        //$pref_site_dominio->setExitAction(new TAction([$this,'onExitDominio']));
        
        /*
        // pegando as templates da pasta de templates
        $dir = "../templates/";
        $arr = [];
        foreach (glob ($dir."*", GLOB_ONLYDIR) as $pastas)
        {
        	if (is_dir ($pastas))
        	{
        		$tmp = str_replace ($dir,"",$pastas);
        		$arr[$tmp] = $tmp;
        	}
        }
        $pref_site_template->addItems($arr); //['theme5'=>'Tema 5']);
        */
        //$this->form->setTabJustified();
        
        // adicionando os campos ao formulário
        $this->form->appendPage('Ajustes Globais');
        $this->form->addFields( [new TFormSeparator('Dados do Site')] );
        $this->form->addFields( [new TLabel('Nome do Site')], [$pref_site_nome], [new TLabel('URL Principal')], [$pref_site_dominio] );
        $this->form->addFields( [new TLabel('Descrição')], [$pref_site_descricao] );
        $this->form->addFields( [new TLabel('Keywords')], [$pref_site_keywords] );
        $this->form->addFields( [new TLabel('Manutenção')], [$pref_site_manutencao], [new TLabel('Linguagem')], [$pref_site_language] );
        $this->form->addFields( [new TLabel('Mensagem')], [$pref_site_mensagem] );
        $this->form->addFields( [new TLabel('Cache Ativo')], [$pref_cache_control] );
        $this->form->addFields( [new TFormSeparator('Dados da Empresa')] );
        $this->form->addFields( [new TLabel('Empresa')], [$pref_emp_nome] );
        $this->form->addFields( [new TLabel('CNPJ')], [$pref_emp_cnpj], [new TLabel('Telefone')], [$pref_emp_fone] );
        $this->form->addFields( [new TLabel('E-mail')], [$pref_emp_email], [new TLabel('Celular')], [$pref_emp_celular] );
        $this->form->addFields( [new TLabel('Endereço')], [$pref_emp_endereco] );
        $this->form->addFields( [new TLabel('Cidade')], [$pref_emp_cidade], [new TLabel('Estado')], [$pref_emp_estado] );
        $this->form->addFields( [new TLabel('País')], [$pref_emp_pais], [new TLabel('Postal')], [$pref_emp_postal] );
        $this->form->addFields( [new TLabel('Mapa (iframe)')], [$pref_emp_maps] );
        $this->form->appendPage('Ajustes do Blog');
        $this->form->addFields( [new TFormSeparator('Configurações do Blog')] );
        $this->form->addFields( [new TLabel('Título')], [$pref_blog_titulo] , [new TLabel('Subtitulo')], [$pref_blog_subtitulo] );
        $this->form->addFields( [new TLabel('Descrição')], [$pref_blog_metadesc] );
        $this->form->addFields( [new TLabel('Keywords')], [$pref_blog_metakey] );
        $this->form->appendPage('Outros Ajustes');
        $this->form->addFields( [new TFormSeparator('Configurações de E-mail')] );
        $this->form->addFields( [new TLabel(_t('Mail from'))], [$pref_mail_from], [new TLabel(_t('SMTP Auth'))], [$pref_smtp_auth] );
        $this->form->addFields( [new TLabel(_t('SMTP Host'))], [$pref_smtp_host], [new TLabel(_t('SMTP Port'))], [$pref_smtp_port] );
        $this->form->addFields( [new TLabel(_t('SMTP User'))], [$pref_smtp_user], [new TLabel(_t('SMTP Pass'))], [$pref_smtp_pass] );
        $this->form->addFields( [new TLabel('E-mail de destino')], [$pref_mail_to], [],[] );
        $this->form->addFields( [new TFormSeparator('APIs Sociais')] );
        $this->form->addFields( [new TLabel('Instagram UserID')], [$pref_instagram_userid], [new TLabel('Instagram TOKEN')], [$pref_instagram_token] );

        

        // ajustando o tamanho
        $pref_site_keywords->setSize('100%',60);
        $pref_site_descricao->setSize('100%',50);
        $pref_site_manutencao->setSize('100%');
        $pref_cache_control->setSize('100%');
        $pref_emp_maps->setSize('100%','auto');
        $pref_blog_metadesc->setSize('100%',50);
        $pref_blog_metakey->setSize('100%',60);
        
        // criando validações
        $pref_site_nome->addValidation('Nome do Site', new TRequiredValidator);
        $pref_site_dominio->addValidation('URL Principal', new TRequiredValidator);
        $pref_emp_nome->addValidation('Empresa', new TRequiredValidator);
        $pref_mail_from->addValidation('E-mail de origem', new TEmailValidator);
        $pref_mail_to->addValidation('E-mail de destino', new TEmailValidator);
        $pref_emp_cnpj->addValidation('CNPJ', new TCNPJValidator);
        $pref_emp_email->addValidation('E-mail', new TEmailValidator);
        
        
        $btn = $this->form->addAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        
        $btn = $this->form->addHeaderAction(_t('Save'), new TAction(array($this, 'onSave')), 'fa:save');
        $btn->class = 'btn btn-sm btn-primary waves-effect';
        
        $container = new TVBox;
        $container->{'style'} = 'width: 100%;';
        $container->add(new TXMLBreadCrumb('menu.xml', __CLASS__));
        $container->add($this->form);
        
        parent::add($container);
    }
    
    /**
     * 
     */
    public static function onChangeManutencao($param)
    {
        if (isset($param['pref_site_manutencao']) AND $param['pref_site_manutencao'] == '1')
        {
            TEntry::enableField('form_Preferencias','pref_site_mensagem');
        }
        else
        {
            TEntry::disableField('form_Preferencias','pref_site_mensagem');
        }
    }
    
    /**
     * 
     *
    public static function onExitDominio($param)
    {
        if ( substr($param['pref_site_dominio'], -1) == '/' )
            $param['pref_site_dominio'] = substr($param['pref_site_dominio'], 0, -1);
        
        $obj = new StdClass;
        $obj->pref_site_dominio = $param['pref_site_dominio'];
        
        TForm::sendData('form_Preferencias', $obj);
    }
    
    /**
     * Carrega o formulário de preferências
     */
    public function onEdit($param)
    {
        try
        {
            // open a transaction with database
            TTransaction::open($this->database);
            
            $preferences = SystemPreference::getAllPreferences();
            if ($preferences)
            {
                // preparando as palavras chave
                $preferences['pref_site_keywords'] = explode(',',$preferences['pref_site_keywords']);
                $preferences['pref_blog_metakey']  = explode(',',$preferences['pref_blog_metakey']);
                
                $this->form->setData((object) $preferences);
                
                if ($preferences['pref_site_manutencao'] == '1')
                    TEntry::enableField('form_Preferencias','pref_site_mensagem');
            } 
            
            // close the transaction
            TTransaction::close();
        }
        catch (Exception $e) // in case of exception
        {
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
    /**
     * method onSave()
     * Executed whenever the user clicks at the save button
     */
    public function onSave()
    {
        try
        {
            // open a transaction with database
            TTransaction::open($this->database);
            //TTransaction::setLogger(new TLoggerSTD); // standard output
            
            $this->form->validate();
            
            // get the form data
            $data = $this->form->getData();
            $data_array = (array) $data;
            
            //montando link whatsapp
            $data_array['pref_emp_whatsapp'] = 'https://wa.me/55'.THelper::urlAmigavel($data_array['pref_emp_celular'],true);
            
            foreach ($data_array as $property => $value)
            {
                if ($property == 'pref_site_keywords' || $property == 'pref_blog_metakey')
                {
                    // preparando as palavras chave
                    $value = implode(',',$value);
                }
                if ( $property == 'pref_site_dominio' )
                {
                    if ( substr($value, -1) == '/' )
                        $value = substr($value, 0, -1);
                }
                $object = new SystemPreference;
                $object->{'id'}    = $property;
                $object->{'value'} = $value;
                $object->store();
            }
            
            // fill the form with the active record data
            $this->form->setData($data);
            
            if ($data_array['pref_site_manutencao'] == '1')
                TEntry::enableField('form_Preferencias','pref_site_mensagem');
            
            // close the transaction
            TTransaction::close();
            
            // atualizamos as midias, se necessário
            if ( !empty($data_array['pref_emp_whatsapp']) )
            {
                TTransaction::open('sistema');
                Midia::setWhatsAppURL($data_array['pref_emp_whatsapp']);
                TTransaction::close();
            }
            
            // shows the success message
            new TMessage('info', AdiantiCoreTranslator::translate('Record saved'));
            // reload the listing
        }
        catch (Exception $e) // in case of exception
        {
            // get the form data
            $object = $this->form->getData($this->activeRecord);
            
            // fill the form with the active record data
            $this->form->setData($object);
            
            // shows the exception error message
            new TMessage('error', $e->getMessage());
            
            // undo all pending operations
            TTransaction::rollback();
        }
    }
    
}
