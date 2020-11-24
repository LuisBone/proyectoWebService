<?php

class Usuarios_Ctrl {
    public $M_Usuario = null;

    public function __construct(){

        $this -> M_Usuario = new M_Usuarios();

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

        $this->M_Usuario->Load(['usuario = ? OR correo = ?', $data['usuario'], $data['correo']]);

        if ($this->M_Usuario->loaded()>0) {
            echo json_encode([
                'mensaje' => 'Ya existe este usuario o correo que intenta registrar',
                'info' => [
                    'id' => 0
                ]
            ]);
        }else{
            $this->M_Usuario->set('usuario', $data['usuario']);
            $this->M_Usuario->set('clave', $data['clave']);
            $this->M_Usuario->set('nombre', $data['nombre']);
            $this->M_Usuario->set('telefono', $data['telefono']);
            $this->M_Usuario->set('correo', $data['correo']);
            $this->M_Usuario->set('activo', $data['activo']);
            $this->M_Usuario->save();
            
            echo json_encode([
                'mensaje' => 'Usuario creado',
                'info' => [
                    'id' => $this->M_Usuario->get('id')
                ]
            ]);
        }
        

    }

    public function consultar($f3){
        $Usuario_id = $f3->get('PARAMS.usuario_id');
        $this->M_Usuario->Load(['id=?',$Usuario_id]);
        $msg= "";
        $item = array();
        if($this->M_Usuario->loaded() > 0){
            $msg = "Usuario encontrado.";
            $item = $this->M_Usuario->cast(); 
        } else {
            $msg = "El Usuario no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => [
                'item' => $item
            ]
        ]);
    }

    public function listado($f3){

        $result = $this->M_Usuario->find(['usuario LIKE ?', '%' . $f3->get('POST.texto') . '%']);
        $items = array();
        foreach($result as $Usuario){
            $items[] = $Usuario->cast();
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
        $Usuario_id = $f3->get('POST.usuario_id');
        $this->M_Usuario->Load(['id=?',$Usuario_id]);
        $msg= "";
        if($this->M_Usuario->loaded() > 0){
            $msg = "Usuario eliminado.";
            $this->M_Usuario->erase(); 
        } else {
            $msg = "El Usuario no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }

    public function actualizar($f3){
        $usuario_id = $f3->get('PARAMS.usuario_id');
        $this->M_Usuario->Load(['id=?',$usuario_id]);
        $msg= "";
        if($this->M_Usuario->loaded() > 0){

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

            $_usuario = new M_Usuarios();
            $_usuario->load(['(usuario = ? OR correo = ?) AND id <> ?', $data['usuario'], $data['correo'], $usuario_id]);

            if($_usuario->loaded() > 0){
                $msg = "El registro no se pudo modificar debido a que el correo o usuario se encuentra en uso.";
            }else{
                $this->M_Usuario->set('usuario', $data['usuario']);
                $this->M_Usuario->set('clave', $data['clave']);
                $this->M_Usuario->set('nombre', $data['nombre']);
                $this->M_Usuario->set('telefono', $data['telefono']);
                $this->M_Usuario->set('correo', $data['correo']);
                $this->M_Usuario->set('activo', $data['activo']);
                $this->M_Usuario->save();
                $msg = "Usuario actualizado.";
            }
        } else {
            $msg = "El Usuario no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }
}