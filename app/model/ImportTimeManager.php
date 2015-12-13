<?php

namespace App\Model;

use Nette;
use Nette\Object;

/**
 * Trida pro vytvoreni prikazu SELECT ze zadanych kriterii
 * @package App\Model
 */
class ImportTimeManager extends Object
{
    /** @var Nette\Database\Context */
    private $database;
       
    private $fp;                // Ukazatel na soubor
    private $id_event;          // Identifikator plaveckeho zavodu
    private $delimiter;         // Oddelovac polozek
    private $length;            // Maximalni delka radku
    // Pole pro ulozeni pozadovanych polozek
    private $data = array();
        
    private $status = array("fileok" => TRUE, "errnum" => 0, "recnum" => 0);

    const NUM_FIELDS = 23;      // Spravny pocet polozek na radku
    const ODDIL = 'ASKBl';      // Zkratka domaciho plaveckeho oddilu
    const STAFETA = 'x';        // Identifikace stafety
    const KRAUL_OLD = 'K';      // Nespravne oznaceni volny zpusob
    const KRAUL_NEW = 'VZ';     // Spravne oznaceni pro volny zpusob
    const POLOHA_OLD = 'O';     // Nespravne oznaceni polohoveho zavodu
    const POLOHA_NEW = 'PZ';    // Spravne oznaceni polohoveho zavodu


    public function __construct(Nette\Database\Context $database)
    { 
        $this->database = $database;        
    } 

    public function __destruct() 
    { 
        if ($this->fp) 
        { 
            fclose($this->fp); 
        } 
    } 

    /**
     * Vybere pouze pozadovane radky 
     * @param array $row
     */
    private function selectRow($row) 
    {  
        // Jen radek se spravnym poctem polozek
        if (count($row) == self::NUM_FIELDS){

            // Jen radek popisujici domaci plavecky oddil
            if ($row[7] == self::ODDIL){

                // Jen radek, ktery se netyka stafet
                if (strpos($row[1], self::STAFETA) === FALSE){
                    // Kontrola vybranych radku
                    $this->checkRow($row);
                }
            }
        }        
    }
    
    /**
     * Zkontroluje a upravi pozadovane sloupce, ty pak ulozi do pole
     * @param array $row
     */
    private function checkRow($row) 
    {  
        // Uprava oznaceni nazvu discipliny (K -> VZ, O -> PZ)
        if (strpos($row[1], self::KRAUL_OLD) == TRUE){
            $row[1] = substr($row[1], 0, -1).self::KRAUL_NEW;
        }
        elseif (strpos($row[1], self::POLOHA_OLD) == TRUE) {
            $row[1] = substr($row[1], 0, -1).self::POLOHA_NEW;
        }

        // Konverze cestiny z Windows-1250 na utf8
        $row[3] = iconv("windows-1250", "utf-8//TRANSLIT", $row[3]);
        $row[4] = iconv("windows-1250", "utf-8//TRANSLIT", $row[4]);

        // Vytvoreni identifikatoru plavce
        $prijmeni = str_pad(strtoupper(substr(iconv("utf-8", "ASCII//TRANSLIT", $row[3]),0,4)),  4, "0");
        $jmeno    = str_pad(strtoupper(substr(iconv("utf-8", "ASCII//TRANSLIT", $row[4]),0,4)),  4, "0");
        $id = $row[5].$prijmeni.$jmeno;  

        // Kontrola identifikatoru plavce
        $id = $this->kontrolaPlavce($id);

        // Kontrola nazvu discipliny
        $disc = $this->kontrolaDiscipliny($disc = $row[1]);

        // Kontrola umisteni
        $umisteni = $this->kontrolaUmisteni($umisteni = $row[2]);

        // Kontrola casu
        $cas = $this->kontrolaCasu($cas = $row[8]);

        // Vypocet bodu
        $point = $this->vypocetBodu($c = $row[8], $id, $disc);        
        
        // Z radku se vyberou pouze pozadovane polozky
        // identifikator, prijmeni, jmeno, rocnik,disciplina, cas, umisteni
        $item = array(
            "id_event" => $this->id_event,
            "id_stroke" => $disc,
            "id_swimmer" => $id,           
            "time" => $cas,
            "rank" => $umisteni,
            "point" => $point
        );    

        // Vybrane polozky se ulozi jako jeden radek pole
        $this->data[] = $item;        
    }
    
    /**
     * Kontroluje existenci identifikatoru v tabulce plavcu
     * @param string $id
     * @return string
     */
    private function kontrolaPlavce($id) 
    {  
        $data = $this->database->query('select id from sm_swimmer where id = ?', $id);
        if ($data->getRowCount() == 0) {
            $id = 'E#'.$id;
            $this->status["errnum"]++;
        }

        return $id;
    }
    
    /**
     * Kontroluje existenci discipliny v tabulce stylu
     * @param string $disc
     * @return string
     */
    private function kontrolaDiscipliny($disc) 
    {  
        $data = $this->database->query('select id from sm_stroke where id = ?', $disc);
        if ($data->getRowCount() == 0) {
            $disc = 'E#'.$disc;
            $this->status["errnum"]++;
        }

        return $disc;
    }

    /**
     * Kontroluje spravny format a hodnoty sloupce Cas
     * @param string $cas
     * @return string
     */
    private function kontrolaCasu($cas) 
    {  
        // Jedna se o diskvalifikaci
        if ($cas === '99:99,9' || $cas === '99:99,99') {
            return $cas;
        }
        
        // Povolene hodnoty pro umisteni jsou 00:00,00 az 99:59,99
        $re='/^[0-9][0-9]:[0-5][0-9],[0-9][0-9]?$/'; 
        
        if(!preg_match($re,$cas)) {
            $cas = 'E#'.$cas;
            $this->status["errnum"]++;
        }   

        return $cas;
    }

    /**
     * Kontroluje spravny format a hodnoty sloupce Umisteni
     * @param string $umisteni
     * @return string
     */
    private function kontrolaUmisteni($umisteni) 
    {  
        // Povolene hodnoty pro umisteni jsou 1 az 999
        $re='/^[1-9][0-9]?[0-9]?$/'; 

        if(!preg_match($re,$umisteni)) {
            $umisteni = 0;
            $this->status["errnum"]++;
        }   
                
        return intval($umisteni);
    }
    
    /**
     * Vypocte bodovou hodnotu zaplavaneho casu
     * @param string $cas   Zaplavany cas
     * @param string $id    Identifikator plavce
     * @param string $disc  Identifikator discipliny
     * @return int          Vypocteny pocet bodu
     */
    private function vypocetBodu($c, $id, $disc) 
    {  
        // Body se pocitaji jen kdyz jsou predchozi polozky bez chyb
        if ($this->status["errnum"] > 0) {
            return 0;
        }
        
        // Dotaz na pohlavi plavce (M - muz, Z - zena)
        $data = $this->database->query('select sex from sm_swimmer where id = ?', $id);
        $sex = $data->fetchField(); 

        // Dotaz na delku bazenu (1 - 50m, 0 - 25m)
        $data = $this->database->query('select lcm from sm_event where id = ?', $this->id_event);
        $lcm = $data->fetchField();
        
        // Dotaz na zakladni cas
        if ($sex == "M" && $lcm == "0") {       // Muzi 25m
            $data = $this->database->query('select bt25_m from sm_stroke where id = ?', $disc);
            $b = $data->fetchField();
        }
        elseif ($sex == "Z" && $lcm == "0") {   // Zeny 25m
            $data = $this->database->query('select bt25_f from sm_stroke where id = ?', $disc);
            $b = $data->fetchField();
        }
        elseif ($sex == "M" && $lcm == "1") {   // Muzi 50m
            $data = $this->database->query('select bt50_m from sm_stroke where id = ?', $disc);
            $b = $data->fetchField();
        }
        else {                                  // Zeny 50m
            $data = $this->database->query('select bt50_f from sm_stroke where id = ?', $disc);
            $b = $data->fetchField();
        }
        
        // Prevod zaplavaneho casu na sekundy (mm:ss,ss)
        $t1 = doubleval(substr($c, 0, 2))*60; // Minuty 
        $t2 = doubleval(substr($c, 3));       // Sekundy
        $t = $t1 + $t2;
        
        // Vlastni vypocet bodu
        $p = round(1000*pow($b/$t, 3));
        
        return $p;
    }
    
    
    /**
     * Ze zvoleneho txt souboru vybere vysledky domacich plavcu
     * @param string $file_name Vstupni txt soubor
     * @param string $event_id Identifikator zavodu
     * @param string $delimiter Oddelovac poli
     * @param int $length Maximalni delka radku
     * @return array Informace o poctu chyb a nactenych zaznamu
     */
    public function getImportData($file_name, $id_event, $delimiter=";", $length=1000) 
    { 
        setlocale(LC_CTYPE, 'cs_CZ.UTF-8');
        // V pripade chyby fopen vrati FALSE
        if (!$this->fp = @fopen($file_name, "r")) {
            $this->status["fileok"] = FALSE;
            return $this->status;
        }
        $this->id_event = $id_event;
        $this->delimiter = $delimiter; 
        $this->length = $length;        

        // Prochazi se vsechny radky vstupniho souboru
        while (($row = fgetcsv($this->fp, $this->length, $this->delimiter)) !== FALSE)
        { 
            // Z radku se nactou pouze pozadovane polozky
            $this->selectRow($row);
        } 
        
        // Ulozi se pocet nactenych zaznamu
        $this->status["recnum"] = count($this->data);
        
        // Vymaz pomocne tabulky
        $this->database->query('TRUNCATE TABLE sm_import');
                
        // Naplneni pomocne tabulky
        $this->database->query('INSERT INTO sm_import', $this->data);
          
        // Vraceni informace o poctu chyb a nactenych zaznamu
        return $this->status; 
    } 
 
}
