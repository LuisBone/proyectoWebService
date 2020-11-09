<?php

class Inicio_Ctrl
{

    public function Obtener_Totales($f3)
    {
        $M_Usuario = new M_Usuarios();
        $M_Cliente = new M_Clientes();
        $M_Pedido = new M_Pedidos();
        $M_Producto = new M_Productos();

        echo json_encode([
            'mensaje' => '',
            'info' => [
                'pedidos' => $M_Pedido->count(),
                'productos' => $M_Producto->count(),
                'clientes' => $M_Cliente->count(),
                'usuarios' => $M_Usuario->count()
            ]
        ]);
    }
}
