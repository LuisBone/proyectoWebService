<?php

class Pedidos_Ctrl {
    public $M_Pedido = null;
    public $M_Pedido_Detalle = null;

    public function __construct(){

        $this -> M_Pedido = new M_Pedidos();
        $this -> M_Pedido_Detalle = new M_Pedidos_Detalle();

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

        //$fecha = $f3->get('POST.fecha');
        //$fecha = explode('.', $fecha)[0];
        //$fecha = str_replace('T', ' ', $fecha);

        $this->M_Pedido->set('cliente_id', $data['cliente_id']);
        $this->M_Pedido->set('fecha', $data['fecha']);
        $this->M_Pedido->set('usuario_id', $data['usuario_id']);
        $this->M_Pedido->set('estado', $data['estado']);
        $this->M_Pedido->save();
        
        echo json_encode([
            'mensaje' => 'Pedido creado',
            'info' => [
                'id' => $this->M_Pedido->get('id')
            ]
        ]);

    }
    
    //agregar productos detalle_pedidos
    public function agregar_producto($f3){

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

        $this->M_Pedido->load(['id = ?', $f3->get('PARAMS.pedido_id')]);
        //echo "<pre>".$f3->get('PARAMS.pedido_id')."</pre>";
        //echo "<pre>".$f3->get('POST.producto_id')."</pre>";
        if ($this->M_Pedido->loaded() > 0){

            $this->M_Pedido_Detalle->load(['pedido_id = ? AND producto_id = ?', $f3->get('PARAMS.pedido_id'), $data['producto_id']);
            
            $existe = $this->M_Pedido_Detalle->loaded() > 0;

            $this->M_Pedido_Detalle->set('pedido_id', $f3->get('PARAMS.pedido_id'));
            $this->M_Pedido_Detalle->set('producto_id', $data['producto_id']);
            $this->M_Pedido_Detalle->set('cantidad', $data['cantidad']);
            $this->M_Pedido_Detalle->set('precio', $data['precio']);

            if (!$existe) {
                $this->M_Pedido_Detalle->save();
                echo json_encode([
                    'mensaje' => 'Producto agregado',
                    'info' => [
                        'id' => $this->M_Pedido_Detalle->get('id')
                    ]
                ]);
            } else {
                $this->M_Pedido_Detalle->update();
                echo json_encode([
                    'mensaje' => 'Producto actualizado',
                    'info' => [
                        'id' => $this->M_Pedido_Detalle->get('id')
                    ]
                ]);
            }
            
            
        } else {
            echo json_encode([
                'mensaje' => 'El pedido no existe.',
                'info' => []
            ]);
        }
    }

    public function consultar($f3){
        $Pedido_id = $f3->get('PARAMS.pedido_id');
        $this->M_Pedido->Load(['id=?',$Pedido_id]);
        $msg= "";
        $item = array();
        if($this->M_Pedido->loaded() > 0){
            $msg = "Pedido encontrado.";
            $item = $this->M_Pedido->cast();

            $this->M_Pedido_Detalle->nombre = 'SELECT nombre FROM productos WHERE id = pedidos_detalle.producto_id';

            $result = $item['items'] = $this->M_Pedido_Detalle->find(['pedido_id = ?', $f3->get('PARAMS.pedido_id')]);
            $cont=0;
            foreach ($result as $r) {
                $item['items'][$cont] = $r->cast();
                $cont++;
            }
            
        } else {
            $msg = "El Pedido no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => [
                'item' => $item
            ]
        ]);
    }

    public function listado($f3){

        $params = [];
        if (!empty($f3->get('POST.texto'))) {
            $params = ['id = ?', $f3->get('POST.texto')];
        }
        $this->M_Pedido->cliente = 'SELECT nombre FROM clientes WHERE id = pedidos.cliente_id';
        $this->M_Pedido->n_productos = 'SELECT COUNT(id) FROM pedidos_detalle WHERE pedido_id = pedidos.id';
        $this->M_Pedido->vendedor = 'SELECT nombre FROM usuarios WHERE id = pedidos.usuario_id';
        $this->M_Pedido->total = 'SELECT SUM(cantidad * precio) FROM pedidos_detalle WHERE pedido_id = pedidos.id';

        $result = $this->M_Pedido->find($params);
        $items = array();
        foreach($result as $Pedido){
            $items[] = $Pedido->cast();
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
        $Pedido_id = $f3->get('POST.pedido_id');
        $this->M_Pedido->Load(['id=?',$Pedido_id]);
        $msg= "";
        if($this->M_Pedido->loaded() > 0){
            $msg = "Pedido eliminado.";
            $this->M_Pedido->erase(); 
        } else {
            $msg = "El Pedido no existe.";
        }
        echo json_encode([
            'mensaje' => $msg,
            'info' => []
        ]);
    }

    //eliminar productos detalle_pedidos
    public function borrar_producto($f3){
        $this->M_Pedido->load(['id = ?', $f3->get('PARAMS.pedido_id')]);
        //echo "<pre>".$f3->get('PARAMS.pedido_id')."</pre>";
        //echo "<pre>".$f3->get('POST.producto_id')."</pre>";
        if ($this->M_Pedido->loaded() > 0){

            $this->M_Pedido_Detalle->load(['pedido_id = ? AND id = ?', $f3->get('PARAMS.pedido_id'), $f3->get('POST.item_id')]);
            
            if( $this->M_Pedido_Detalle->loaded() > 0 ){
                $this->M_Pedido_Detalle->erase();
                echo json_encode([
                    'mensaje' => 'Producto borrado.',
                    'info' => null
                ]);    
            } else {
                echo json_encode([
                    'mensaje' => 'Producto no existe.',
                    'info' => null
                ]);
            }           
            
        } else {
            echo json_encode([
                'mensaje' => 'El pedido no existe.',
                'info' => []
            ]);
        }
    }
}