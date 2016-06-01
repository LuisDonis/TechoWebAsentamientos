<?php
/**
 * 
 * El sistema de Mapas del La Unidad de Planificacion Estrategica,
 * tiene como objetivo, administrar, actualizar y gestionar el estado de la red
 * de servicios de salud en sus diferentes niveles, asi como geolocalizar cada uno
 * de los servicios en un mapa con Google maps.
 * Este Sistema sera usado por la Unidad de Planificacion Estrategica (UPE)
 * del Ministerio de Salud Publica y Asistencia Social (MSPAS).
 * 
 * @version        1.0
 * @copyright      Copyright (C) Sistema de Información Gerencial de Salud (SIGSA), Ministerio de Salud Publica (MSPAS) - Todos los Derechos Reservados.
 * @author         JBaires (Juan Jose Ajcuc Baires) 
 */

//Se inicializan las Sesiones
session_start();
//$_SESSION['usuario'] = true;
/** 
 * Se instancia en 1 o true para indicar que este es un archivo padre y desde el cual se pueden llamar y ejecutar otros controladores y modelos. * 
 * @param boolean
 * 
 */
define( '_SYSEXEC', 1 );

/**
 * Se Define PATH_BASE para crear una variable estatica que contendra la ruta base desde donde se ejecutaran todos los ficheros
 * o desde donde se mandaran a traer las librerias y controladores
 * @param       function
 */
define('PATH_BASE', dirname(__FILE__) );

/**
 * Se define la constante DS que contendra en separador de directorios en funcion al sistema operativo
 * @param       Constante
 */
define( 'DS', DIRECTORY_SEPARATOR );
require_once ( PATH_BASE .DS.'includes'.DS.'define.php' );

require_once ( P_INCLUDES . DS .'required.php' );
/*$system = new Framework();
$system->display->deployContent();*/



$dbo = new Dbexec();

$dbo->queryExecute("SHOW tables;");
if($dbo->error){
    echo $dbo->error;
}else{    
    while($row = $dbo->getArray()){
        $code = "";
        
        echo "<textarea cols=190 rows=10>";
        $table_name = $row[0];
$code .= "<?php
class {$table_name} extends OrmClass{
    \tprotected \$_datasource = \"{$table_name}\";\r";
        $dbInside = new Dbexec();
        $dbInside->queryExecute("describe {$table_name}");
        $table = array();
        $functionsSets = "";
        while($r = $dbInside->getArray()){
            $code .= "\tpublic \${$r[Field]} = Array (";
            if($r['Type'] != 'text'){                
                $tipo = explode(" ",$r['Type']);
                $tipo[0];
                $posini = strpos($tipo[0], "(")+1;
                $posfin = strpos($tipo[0], ")");
                $distancia = $posfin - $posini;
                $max_size = substr($tipo[0], $posini,$distancia);
                $fieldtipo = substr($tipo[0], 0,$posini-1);                
                $code .= "'type' => '{$fieldtipo}'";
                
                
                if($fieldtipo=="enum"){
                    
                    $initValuesPos = strpos($r['Type'], "(")+1;
                    $endValuesPos = strpos($r['Type'], ")");
                    $valuesDistnacia = $endValuesPos - $initValuesPos;
                    //$initValuesPos = 
                    
                    $code .= ", 'values' => array(".substr($r['Type'], $initValuesPos,$valuesDistnacia).")";
                }
                
                
                if(in_array('unsigned', $tipo)){
                    $code .= ", 'size' => '{$max_size}'"; 
                    $code .= ", 'unsigned' => TRUE";
                     
                }
                
                if(in_array('zerofill', $tipo)){
                    $code .= ", 'size' => '{$max_size}'";
                    $code .= ", 'zerofill' => 'TRUE'";
                }
                
                
                
                
                
            }else{
                $code .= "'type' => '{$r[Type]}'";
            }
             $code .= ", 'null' =>  '";
             $code .= $r['Null'];
             $code .= "'";
             
             if($r['Key']=="PRI"){
                 $code .= ", 'primary' => TRUE";
             }elseif($r['Key']=="MUL"){
                 $code .= ", 'foreign' => TRUE";
                 $code .= ", 'reference' => '" . str_replace('_id', '', $r['Field']). "'";
             }
             
             if( $r['Default'] ){
                 $code .= ", 'default' => '".$r['Default']."'";
             }
             
             if($r['Extra']=="auto_increment"){
                 $code .= ", 'auto_increment' => TRUE";
             }             
             $code .= ", 'val'=>''); \r" ;
             $campo = str_replace(" ", "", ucwords(str_replace("_", " ", $r['Field'])) );
             $functionsSets .= 
             "\tfunction set{$campo}(\$var){
                \$this->{$r[Field]}['val'] = \$var;
             }\r";
             
             $functionsSets .= 
             "\tfunction get{$campo}(){
                return \$this->{$r[Field]}['val'];
             }\r";
            
        }

        $code .= "\t".'function getReference() {
            return $this->_datasource;
        }'."\r";
        
        $code .= $functionsSets;
        
        $code .= "}";
        echo $code;
        echo "</textarea><br />";
        $myFile = P_MODELOS.DS.$table_name.".php";        
        $fh = fopen($myFile, 'w') or die("can't open file");        
        fwrite($fh, $code);
        
        fclose($fh);
       //chmod(P_MODELOS, "0755");
        
    }
}



?>