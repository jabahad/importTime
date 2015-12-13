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
        // 1. Vlozeni noveho zaznamu do tabulky se zavody
        
        // 2. Vyber zavodu (identifikatoru), vetsinou toho, ktery byl vytvoren v predchozim bode
        $id_event = "VCHOD2015";
        
        // 3. Kontrola identifikatoru zavodu - nesmi byt pouzit v tabulce casu
        $data = $this->database->query('select id from sm_time where id_event = ?', $id_event);
        if ($data->getRowCount() > 0) {
            // Chyba! Vyzadovat novou volbu zavodu
            // Pozor! VCHOD2015 jiz v tabulce casu je!
        }
    
        // 4. Zadani textoveho souboru s vysledky s predepsanym DR
//        $input_file = "/home/vencs88/php/import_time/data/cp_praha2015.txt";
//        $input_file = "/home/vencs88/php/import_time/data/vysledky_blbec.txt";
        $input_file = "/home/vencs88/php/import_time/data/mem2015.txt";
        
        // 5. Vizualni kontrola chyb po nacteni vysledku do pomocne tabulky
        $this->template->status = $this->importTimeManager->getImportData($input_file, $id_event);        
        $this->template->data = $this->database->table('sm_import');
        
        // 6. Nejsou-li chyby, umoznit import vysledku do tabulky casu
//        INSERT INTO sm_time
//            (
//                id_stroke,
//                id_swimmer,
//                time,
//                rank,
//                point
//            ) 
//            SELECT * FROM sm_import;
//        
//        // Zvazit vymaz pomocne tabulky
//        DELETE FROM sm_import;
        
    }

}
