<?php

class Clientes_Ctrl {
    
    public $M_Cliente = null;

    public function __construct(){

        $this -> M_Cliente = new M_Clientes();

    }

    public function crear($f3){

        //-------inicio---------RECIBIR DATOS RAW JSON------------------
            if ($f3->VERB == 'POST' && preg_match('/json/',$f3->get('HEADERS[Content-Type]')))
            {
               $f3->set('BODY', file_get_contents('php://input'));
               if (strlen($f3->get('BODY'))) {
                  $data = json_decode($f3->get('BODY'),true);
                  if (json_last_error() == JSON_ERROR_NONE) {
                     $f3->set('Error',$data);
                  }
               }
            }
        //-------fin---------RECIBIR DATOS RAW JSON------------------ 

        $this->M_Cliente->Load(['identificacion = ? OR correo = ?', $data['identificacion'], $data['correo']]);

        if ($this->M_Cliente->loaded()>0) {
            echo json_encode([
                'mensaje' => 'Ya existe un cliente con la identificación o correo que intenta registrar',
                'info' => [
                    'id' => 0
                ]
            ]);
        }else{
            
            $this->M_Cliente->set('identificacion', $data['identificacion']);
            $this->M_Cliente->set('nombre', $data['nombre']);
            $this->M_Cliente->set('telefono', $data['telefono']);
            $this->M_Cliente->set('correo', $data['correo']);
            $this->M_Cliente->set('direccion', $data['direccion']);
            $this->M_Cliente->set('pais', $data['pais']);
            $this->M_Cliente->set('ciudad', $data['ciudad']);
            $this->M_Cliente->set('activo', $data['activo']);
            $this->M_Cliente->save();
            
            echo json_encode([
                'mensaje' => 'Cliente creado',
                'info' => [
                    'id' => $this->M_Cliente->get('id')
                ]
            ]);
        }


    }

    public function consultar($f3){
        $cliente_id = $f3->get('PARAMS.cliente_id');
        $this->M_Cliente->Load(['id=?',$cliente_id]);
        $msg= "";
        $item = array();
        if($this->M_Cliente->loaded() > 0){
            $msg = "Cliente encontrado.";
            $item = $this->M_Cliente->cast(); 
        } else {
            $msg = "El Cliente no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => [
                'item' => $item
            ]
        ]);
    }

    public function listado($f3){

        $result = $this->M_Cliente->find(['nombre LIKE ?', '%' . $f3->get('POST.texto') . '%']);
        $items = array();
        foreach($result as $Cliente){
            $items[] = $Cliente->cast();
        }
        echo json_encode([
            'mensaje' => count($items) > 0 ? '' : 'Aún no hay registros para mostrar.',
            'info' => [
                'items' => $items,
                "total" => count($items)
            ]
        ]);
        
    }

    public function eliminar($f3){
        $Cliente_id = $f3->get('POST.cliente_id');
        $this->M_Cliente->Load(['id=?',$Cliente_id]);
        $msg= "";
        if($this->M_Cliente->loaded() > 0){
            $msg = "Cliente eliminado.";
            $this->M_Cliente->erase(); 
        } else {
            $msg = "El Cliente no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }

    public function actualizar($f3){

        //-------inicio---------RECIBIR DATOS RAW JSON------------------
        if ($f3->VERB == 'POST' && preg_match('/json/',$f3->get('HEADERS[Content-Type]')))
        {
           $f3->set('BODY', file_get_contents('php://input'));
           if (strlen($f3->get('BODY'))) {
              $data = json_decode($f3->get('BODY'),true);
              if (json_last_error() == JSON_ERROR_NONE) {
                 $f3->set('Error',$data);
              }
           }
        }
        //-------fin---------RECIBIR DATOS RAW JSON------------------

        $cliente_id = $f3->get('PARAMS.cliente_id');
        $this->M_Cliente->Load(['id=?',$cliente_id]);
        $msg= "";

        if($this->M_Cliente->loaded() > 0){            

            $cliente = new M_Clientes();
            //$cliente->Load(['correo = ? AND id <> ?',$data['correo'],$cliente_id]);
            $cliente->Load(['(correo = ? OR identificacion = ?) AND id <> ?',$data['correo'],$data['identificacion'],(int)$cliente_id]);

            if($cliente->loaded() > 0){
                $msg = "El registro no se pudo modificar debido a que el correo se encuentra en uso por otro cliente.";
            }else{
                
                $this->M_Cliente->set('identificacion', $data['identificacion']);
                $this->M_Cliente->set('nombre', $data['nombre']);
                $this->M_Cliente->set('telefono', $data['telefono']);
                $this->M_Cliente->set('correo', $data['correo']);
                $this->M_Cliente->set('direccion', $data['direccion']);
                $this->M_Cliente->set('pais', $data['pais']);
                $this->M_Cliente->set('ciudad', $data['ciudad']);
                $this->M_Cliente->set('activo', $data['activo']);
                $this->M_Cliente->save();
                $msg = "Cliente actualizado.";
            }
        } else {
            $msg = "El Cliente no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }

}