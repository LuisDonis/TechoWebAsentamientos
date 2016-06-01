<?php

/**
 * Esta es la clase padre, con ella se podran realizar tareas a un nivel exterior a los objetos relacionales que surjar del proceso de ORM que se realiza al inicio.
 * Esta clase tiene como objetivo dar herramientas adicionales para el manejo de las clases de manera innerente para poder optimizar la creacion y manipulación de formularios, entre otros.
 * Esta Clase no se debe instancear directamente ya que sin el objeto relacional no tiene mayor sentido.
 * 
 * @author juan Baires
 * @version 1.0
 * 
 */
class Orm {

    /**
     * Agrega un parametro extra como atributo del campo
     * El primer parametro define el nombre que se le asignara al atributo.
     * El segundo parametro asigna el valor a dicho atributo
     * @param   String;         
     * @param   String; 
     */
    function addToAttrs($attrName, $attrValue) {
        $fieldAttrs['_attrs'][$attrName] = $attrValue;
    }

    function constructForm($formId, $formDestination, $formLegend, $formMethod = "post", $formSubmitButtonLabel = "Enviar", $showPK = false, $extraProperties = false) {

        $form = "<form accept-charset=\"utf-8\" class=\"form-horizontal\" id=\"{$formId}\" method=\"{$formMethod}\" action=\"{$formDestination}\"";
        if (is_array($extraProperties) && count($extraProperties) > 0) {
            foreach ($extraProperties as $prop => $propVal) {
                $form .="{$prop}=\"{$propVal}\" ";
            }
        }
        $form .=" >";
        $form .="<fieldset><legend>$formLegend</legend>";
        $form .= $this->constructBodyFrom($showPK);
        $form .= '<div class="control-group">
                        <div class="controls">
                          
                          <button type="submit" class="btn">' . $formSubmitButtonLabel . '</button>
                        </div>
                      </div>
                  </fieldset></form>';
        return $form;
    }

    function constructBodyFrom($fieldForPK = FALSE) {
        $thisAttrs = get_object_vars($this);

        if (!is_null($thisAttrs)) {
            //echo '<pre>';print_r($thisAttrs);echo '</pre>';
            $campos = "";

            $fieldsNames = array();
            $fieldsArrayValues = array();

            foreach ($thisAttrs AS $objp => $v) {

                $fieldsNames[] = $objp;
                $fieldsArrayValues[] = $v;
            }
            for ($i = 1; $i < count($fieldsArrayValues); $i++) {
                //echo '<pre>'.$fieldsNames[$i]."<br />";print_r($fieldsArrayValues[$i]);echo '</pre>';
                if ($fieldForPK || $fieldsArrayValues[$i]['primary'] != TRUE) {
                    $campos .= $this->attrToField($fieldsArrayValues[$i], $fieldsNames[$i]);
                } elseif ($fieldForPK == 'disable') {
                    $campos .= $this->attrToField($attr, $name);
                }
            }


            return $campos;
        } else {
            return false;
        }
    }

    function attrToField($attr, $name) {
        $fieldAttrs = array();
        $fieldAttrs['cssClass'] = "";

        // Verifico el tipo de campo que es para definirlo en el atributo de clase y luego validarlo con JavaScript; 
        // para definir el tipo de dato que puede contener el campo
        switch ($attr['type']) {
            case 'varchar':
                $fieldAttrs['cssClass'] .= " stringField";
                break;
            case 'int':
                $fieldAttrs['cssClass'] .= " intField";
                break;
            case 'text':
                $fieldAttrs['cssClass'] .= " textField";
                break;
            case 'date':
                $fieldAttrs['cssClass'] .= " dateField";
                break;
            case 'tinyint':
                $fieldAttrs['cssClass'] .= " tinyintField";
                break;
            case 'time':
                $fieldAttrs['cssClass'] .= " timeField";
                break;
        }

        //para saber si usa valores positivos y/o negativos
        switch ($attr['unsigned']) {
            case TRUE:
                $fieldAttrs['cssClass'] .= " unsignedField";
                break;
            case FALSE:
                $fieldAttrs['cssClass'] .= " signedField";
                break;
        }

        // Para saber si el campo permite valores nulos o no nulos (para identificar los campos obligatorios)
        switch ($attr['null'] == 'NO') {
            case TRUE:
                $fieldAttrs['cssClass'] .= " notNulleable";
                $fieldAttrs['required'] = "required";
                break;
            case FALSE:
                $fieldAttrs['cssClass'] .= " Nulleable";
                break;
        }

        if ($attr['size'] > 0) {
            $fieldAttrs['maxlength'] = $attr['size'];
        }

        $fieldAttrs['placeholder'] = ucwords(str_replace("_", " ", $name));

        $fieldAttrs['name'] = $name;
        $fieldAttrs['id'] = ucwords(str_replace("_", " ", $name));
        $fieldAttrs['id'] = str_replace("_", " ", $fieldAttrs['id']);

        $input = "input - ";

        if (($attr['type'] != 'text' && $attr['type'] != 'tinyint') && ($attr['type'] != 'blob' && $attr['type'] != 'longtext')) {
            if ($attr['foreign']) {

                if ($attr['parentFilter'] == true) {

                    $fks = $this->getParentReferences($attr['reference']);
                    if (count($fks) > 0) {
                        $parentSelects = $this->constructParentsSelectors($fks, $attr["reference"]);
                    }

                    $field = $parentSelects . '<div class="control-group">
                            <label class="control-label">' . ucfirst(str_replace("_", " ", $attr['reference'])) . '</label>
                            <div class="controls">

                              <select id="combo_' . ucwords(str_replace("_", " ", $attr["reference"])) . '" name="' . $name . '" >
                                
                              </select>

                            </div>
                          </div>';
                } else {
                    MasterController::requerirClase("MysqlSelect");
                    $sl = new MysqlSelect();
                    $sl->setTableReference($attr['reference']);

                    if (is_array($attr['referenceFilter']) && count($attr['referenceFilter']) > 0) {
                        foreach ($attr['referenceFilter'] as $filter) {
                            //echo "<pre>";print_r($filter);echo "</pre>";
                            $sl->addFilter($attr['reference'], $filter['field'], $filter['value'], $filter['exp']);
                        }
                    }
                    //echo "<pre>";print_r($attr);echo "</pre>";
                    //echo $attr['referenceValue'];
                    if ($attr['referenceValue'] && $attr['referenceLabel']) {

                        $sl->addSelection($attr['reference'], $attr['referenceValue']);
                        $sl->addSelection($attr['reference'], $attr['referenceLabel']);
                        if ($sl->execute()) {
                            $selectOptions = "";
                            // echo $sl->query;
                            //echo '<pre>';print_r($sl->rows);echo '</pre>';

                            foreach ($sl->rows as $r) {
                                //echo '123';
                                $selectOptions .= '<option ' . ($r[$attr['referenceValue']] == $attr['val'] ? 'selected="selected"' : '') . ' value="' . $r[$attr['referenceValue']] . '">' . $r[$attr['referenceLabel']] . '</option>"';
                            }
                        } else {
                            
                        }
                    } else {
                        if (($sl->execute() && is_array($sl->rows)) && (count($sl->rows) > 0)) {
                            $selectOptions = "";
                            foreach ($sl->rows as $r) {
                                $selectOptions .= '<option value="' . $r[$attr[0]] . '">' . $r[$attr[1]] . '</option>"';
                            }
                        }
                    }



                    $field = '<div class="control-group">
                            <label class="control-label">' . ucfirst(str_replace("_", " ", $attr['reference'])) . '</label>
                            <div class="controls">

                              <select id="select_' . ucwords(str_replace("_", " ", $attr["reference"])) . '_combo_box" name="' . $name . '" >
                                ' . $selectOptions . '
                              </select>

                            </div>
                          </div>';
                }
            } else {
                if (is_array($attr['extraClasses']) && count($attr['extraClasses']) > 0) {
                    foreach ($attr['extraClasses'] as $extraClass) {
                        $fieldAttrs["cssClass"] .= " $extraClass";
                    }
                }



                $input = "<input class=\"{$fieldAttrs[cssClass]}\" maxsize=\"{$fieldAttrs[maxlength]}\" ";
                if ($fieldAttrs['required'] == "required") {
                    $input .= " required=\"{$fieldAttrs[required]}\" ";
                }
                if (is_array($attr['extraAttr']) && count($attr['extraAttr']) > 0) {
                    foreach ($attr['extraAttr'] as $extraAttr => $extraAttrVal) {
                        $input .= " {$extraAttr}=\"{$extraAttrVal}\" ";
                    }
                }

                $input .= " placeholder=\"{$fieldAttrs[placeholder]}\" ";
                $input .= " id=\"{$fieldAttrs[id]}\" ";
                $input .= " name=\"{$fieldAttrs[name]}\" value=\"{$attr[val]}\" />";

                $field = '<div class="control-group">
                        <label class="control-label" for="inputEmail">' . ($fieldAttrs['id'] = ucfirst(str_replace("_", " ", $name))) . '</label>
                        <div class="controls">
                          ' . $input . '
                        </div>
                      </div>';
            }
        } elseif ($attr['type'] != 'tinyint' && $attr['type'] != 'blob') {
            if (is_array($attr['extraClasses']) && count($attr['extraClasses']) > 0) {
                foreach ($attr['extraClasses'] as $extraClass) {
                    $fieldAttrs["cssClass"] .= " $extraClass";
                }
            }

            $input = "<textarea class=\"{$fieldAttrs[cssClass]}\" ";
            if ($fieldAttrs['required'] == "required") {
                $input .= " required=\"{$fieldAttrs[required]}\" ";
            }

            if (is_array($attr['extraAttr']) && count($attr['extraAttr']) > 0) {
                foreach ($attr['extraAttr'] as $extraAttr => $extraAttrVal) {
                    $input .= " {$extraAttr}=\"{$extraAttrVal}\" ";
                }
            }

            $input .= " placeholder=\"{$fieldAttrs[placeholder]}\" ";
            $input .= " id=\"{$fieldAttrs[id]}\" ";
            $input .= " name=\"{$fieldAttrs[name]}\" >" . $attr['val'] . "</textarea>";

            $field = '<div class="control-group">
                    <label class="control-label" for="inputEmail">' . ($fieldAttrs['id'] = ucfirst(str_replace("_", " ", $name))) . '</label>
                    <div class="controls">
                      ' . $input . '
                    </div>
                  </div>';
        } elseif ($attr['type'] == 'tinyint') {

            if (is_array($attr['extraClasses']) && count($attr['extraClasses']) > 0) {
                foreach ($attr['extraClasses'] as $extraClass) {
                    $fieldAttrs["cssClass"] .= " $extraClass";
                }
            }

            $input = "<select class=\"{$fieldAttrs[cssClass]}\" ";
            if ($fieldAttrs['required'] == "required") {
                $input .= " required=\"{$fieldAttrs[required]}\" ";
            }
            $input .= " placeholder=\"{$fieldAttrs[placeholder]}\" ";
            $input .= " id=\"{$fieldAttrs[id]}\" ";
            $input .= " name=\"{$fieldAttrs[name]}\" >";
            $input .= '<option ' . ($attr['val'] == 1 ? 'selected="selected"' : '') . ' ' . ($attr['val'] == '' ? 'selected="selected"' : '') . ' value="1">Activo</option>';
            $input .= '<option ' . ($attr['val'] == '0' ? 'selected="selected"' : '') . ' value="0">Inactivo</option>';
            $input .= '</select>';

            //$input .= "";

            $field = '<div class="control-group">
                    <label class="control-label" for="inputEmail">' . ($fieldAttrs['id'] = ucfirst(str_replace("_", " ", $name))) . '</label>
                    <div class="controls">
                      ' . $input . '
                    </div>
                  </div>';
        }

        return $field;



        //return $fieldAttrs;
    }

    function postToObject() {
        if ($_POST) {
            foreach ($_POST AS $p => $v) {
                $functionName = "set" . str_replace(" ", "", ucwords(str_replace("_", " ", $p)));
                if (method_exists($this, $functionName)) {
                    $this->$functionName($v);
                }
            }
        }
    }

    function validatePost(array $f) {
        foreach ($f AS $v) {
            if ($this->$v['null'] == 'NO' && empty($this->$v['val'])) {
                return false;
            } else {
                return true;
            }
        }
    }

    function validateObject($validatePK = False) {

        //return get_object_vars($this);
        $f = get_object_vars($this);
        foreach ($f AS $i => $v) {
            if ($i != '_datasource') {
                $array = $this->$i;
                $function = $this->returnfunctionGet($i);
                if ($validatePK && array_key_exists("primary", $array) && $array['primary'] == TRUE) {
                    $exitsKey = array_key_exists("null", $array);
                    if ($exitsKey) {
                        //echo $i . " - " . $this->$function() . "<br />";
                        if ($array['null'] == 'NO' && !$this->$function()) {
                          return false;
                          }
                    }
                }elseif(!array_key_exists("primary", $array) || $array['primary'] != TRUE){
                    $exitsKey = array_key_exists("null", $array);
                    if ($exitsKey) {
                        //echo $i . " - " . $this->$function() . "<br />";
                        if ($array['null'] == 'NO' && !$this->$function()) {
                          return false;
                          }
                    }
                }
            }
        }
        
        return true;
    }

    function getParentReferences($fk) {
        MasterController::requerirModelo($fk);
        $item = new $fk();
        $thisAttrs = get_object_vars($item);
        $fks = array();
        foreach ($thisAttrs AS $attr) {
            if ($attr['foreign'] == true) {
                $fks[] = $attr["reference"];
            }
        }
        return $fks;
    }

    function constructParentsSelectors(array $fks, $refattr) {
        $selectors = "";
        foreach ($fks AS $ref) {
            $value = $ref . "_id";
            $label = 'nombre';
            MasterController::requerirClase("MysqlSelect");
            $sl = new MysqlSelect();
            $sl->setTableReference($ref);
            $sl->addSelection($ref, $value, "val");
            $sl->addSelection($ref, $label, "label");
            $sl->execute();
            if (count($sl->rows) > 0) {
                $opts = "<select name=\"{$ref}_id\" id=\"combo_{$ref}\" class=\"parentFilterCombo\" ref=\"{$refattr}\">";
                foreach ($sl->rows AS $r) {
                    $opts .="<option value\"{$r[val]}\">{{$r[label]}}</option>";
                }
                $opts .= "</select>";
            }
            $selectors .= '<div class="control-group">
                    <label class="control-label" for="inputEmail">' . (ucfirst(str_replace("_", " ", $ref))) . '</label>
                    <div class="controls">
                      ' . $opts . '
                    </div>
                  </div>';
        }

        return $selectors;
    }

    function getPKS() {
        $thisAttrs = get_object_vars($this);
        $pks = array();
        foreach ($thisAttrs AS $attr => $propVal) {
            if (isset($propVal['primary']) && $propVal['primary'] == true && $attr != "_datasource") {
                $pks[] = $attr;
            }
        }
        return $pks;
    }

    function getValuesBySetedId() {
        $thisAttrs = get_object_vars($this);
        MasterController::requerirClase("MysqlSelect");
        $sl = new MysqlSelect();
        $sl->setTableReference($this->getReference());
        $pks = $this->getPKS();
        //print_r($pks);
        foreach ($pks AS $pk) {
            $fieldFunction = $this->returnfunctionGet($pk);
            $val = $this->$fieldFunction();
            $sl->addFilter($this->getReference(), $pk, $val, "=");
        }
        $sl->addSimpleLimit(1);
        $sl->execute();
        if (count($sl->rows) > 0) {
            foreach ($thisAttrs AS $attr => $value) {
                if ($attr != "_datasource") {
                    $fieldFunction = $this->returnfunctionSet($attr);
                    $this->$fieldFunction($sl->rows[0][$attr]);
                }
            }
        }
    }

    function returnfunctionSet($property) {
        return "set" . str_replace(" ", "", ucwords(str_replace("_", " ", $property)));
    }

    function returnfunctionGet($property) {
        return "get" . str_replace(" ", "", ucwords(str_replace("_", " ", $property)));
    }

    function getList(array $selection = array(), array $filters = array()) {

        if (method_exists($this, 'getReference')) {

            MasterController::requerirClase("MysqlSelect");
            $sl = new MysqlSelect();
            $sl->setTableReference($this->getReference());
            $table = $this->getReference();
            if (count($selection) > 0) {
                foreach ($selection AS $field) {
                    $sl->addSelection($table, $field);
                }
            }
            if (count($filters) > 0) {
                foreach ($filters AS $field => $value) {
                    $sl->addFilter($table, $field, $value[0], $value[1]);
                }
            }
            if ($sl->execute()) {
                return $sl->rows;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

}

?>