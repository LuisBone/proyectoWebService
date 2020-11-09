<?php

class Productos_Ctrl {
    
    public $M_Producto = null;

    public function __construct(){

        $this -> M_Producto = new M_Productos();

    }

    public function crear($f3){

        $this->M_Producto->set('codigo', $f3->get('POST.codigo'));
        $this->M_Producto->set('nombre', $f3->get('POST.nombre'));
        $this->M_Producto->set('stock', $f3->get('POST.stock'));
        $this->M_Producto->set('precio', $f3->get('POST.precio'));
        $this->M_Producto->set('activo', $f3->get('POST.activo'));
        $this->M_Producto->save();
        
        echo json_encode([
            'mensaje' => 'Producto creado',
            'info' => [
                'id' => $this->M_Producto->get('id')
            ]
        ]);

    }

    public function consultar($f3){
        $producto_id = $f3->get('PARAMS.producto_id');
        $this->M_Producto->Load(['id=?',$producto_id]);
        $msg= "";
        $item = array();
        if($this->M_Producto->loaded() > 0){
            $msg = "Producto encontrado.";
            $item = $this->M_Producto->cast(); 
        } else {
            $msg = "El producto no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => [
                'item' => $item
            ]
        ]);
    }

    public function listado($f3){

        $result = $this->M_Producto->find(['nombre LIKE ?', '%' . $f3->get('POST.texto') . '%']);
        $items = array();
        foreach($result as $producto){
            $items[] = $producto->cast();
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
        $producto_id = $f3->get('POST.producto_id');
        $this->M_Producto->Load(['id=?',$producto_id]);
        $msg= "";
        if($this->M_Producto->loaded() > 0){
            $msg = "Producto eliminado.";
            $this->M_Producto->erase(); 
        } else {
            $msg = "El producto no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }

    public function actualizar($f3){
        $producto_id = $f3->get('PARAMS.producto_id');
        $this->M_Producto->Load(['id=?',$producto_id]);
        $msg= "";
        //$estado = 0;
        $info = array();
        if($this->M_Producto->loaded() > 0){

            $_producto = new M_Productos();
            $_producto->load(['codigo = ? AND id <> ?', $f3->get('POST.codigo'), $producto_id]);

            if($_producto->loaded() > 0){
                $msg = "El registro no se pudo modificar debido a que el codigo se encuentra en uso por otro producto.";
                //$estado = -1;
            }else{
                $this->M_Producto->set('codigo', $f3->get('POST.codigo'));
                $this->M_Producto->set('nombre', $f3->get('POST.nombre'));
                $this->M_Producto->set('stock', $f3->get('POST.stock'));
                $this->M_Producto->set('activo', $f3->get('POST.activo'));
                $this->M_Producto->set('precio', $f3->get('POST.precio'));
                $this->M_Producto->save();
                $msg = "Producto actualizado.";
                $info['id'] = $this->M_Producto->get('id');

            }
        } else {
            $msg = "El Producto no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }

}