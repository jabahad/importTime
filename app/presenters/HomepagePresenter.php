<?php

namespace App\Presenters;

use Nette, App\Model\ImportTimeManager;


/**
 * Homepage presenter.
 */
class HomepagePresenter extends BasePresenter
{
    private $importTimeManager;
    
    /** @var Nette\Database\Context */
    private $database;

    
    public function __construct(ImportTimeManager $importTimeManager, Nette\Database\Context $database)
    {
        parent::__construct();
        $this->importTimeManager = $importTimeManager;
        $this->database = $database;
    }
    

    public function renderDefault()
    {
        $id_event = "VCHOD2015";
//        $input_file = "/home/vencs88/php/import_time/data/cp_praha2015.txt";
//        $input_file = "/home/vencs88/php/import_time/data/vysledky_blbec.txt";
        $input_file = "/home/vencs88/php/import_time/data/mem2015.txt";
        
        $this->template->status = $this->importTimeManager->getImportData($input_file, $id_event);        
        $this->template->data = $this->database->table('sm_import');
    }

}
