<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2015   Luis Miguel Pérez Romero   luismipr@gmail.com
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 * 
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 * 
 */


require_model('proveedor_sms.php');
require_model('cliente.php');
require_model('servicio_cliente.php');
require_model('pedido_cliente.php');
require_model('detalle_servicio.php');

/**
 * Description of enviar_sms
 *
 * @author luismi
 */
class enviar_sms extends fs_controller
{

    public $provsms;
    public $cliente;
    public $telefono;
    public $mensaje;
    public $documento;
    public $id;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'Enviar SMS', 'ventas', FALSE, FALSE);
    }

    protected function private_core()
    {

        $this->provsms = new proveedor_sms();
        $this->documento = FALSE;
        $cliente = new cliente();



        if (isset($_GET['servicio']))
        {
            $serv = new servicio_cliente();
            $this->servicio = $serv->get($_REQUEST['id']);
            if ($this->servicio)
            {
                $this->cliente = $cliente->get($this->servicio->codcliente);
                $this->id = $this->servicio->idservicio;
                $this->documento = 'srv';
            }
        }
        else if (isset($_GET['pedido']))
        {
            $serv = new pedido_cliente();
            $this->pedido = $serv->get($_REQUEST['id']);
            if ($this->pedido)
            {
                $this->cliente = $cliente->get($this->pedido->codcliente);
                $this->id = $this->pedido->idpedido;
                $this->documento = 'ped';
            }
        }

        if (isset($_GET['enviar']))
        {

            $this->provsms = $this->provsms->get($_POST['proveedor']);
            $this->telefono = $_POST['telefono'];
            $this->mensaje = $_POST['mensaje'];
            if ($this->provsms->enviar_sms($this->telefono, $this->mensaje))
            {
                $this->new_message('Mensaje enviado');
                if (isset($_GET['srv']))
                {
                    $serv = new servicio_cliente();
                    $this->servicio = $serv->get($_REQUEST['srv']);
                    $this->agrega_detalle();
                }
            }
            else
            {
                $this->new_error_msg('Error al enviar el mensaje');
            }
        }

        $this->share_extensions();
    }

    private function share_extensions()
    {
        $fsext = new fs_extension();
        $fsext->name = 'enviar_sms_servicio';
        $fsext->from = __CLASS__;
        $fsext->to = 'ventas_servicio';
        $fsext->type = 'button';
        $fsext->text = '<span class="glyphicon glyphicon-phone" aria-hidden="true"></span>'
                . '<span class="hidden-xs">&nbsp; Enviar SMS</span>';
        $fsext->params = '&servicio=TRUE';
        $fsext->save();

        $fsext2 = new fs_extension();
        $fsext2->name = 'enviar_sms_pedido';
        $fsext2->from = __CLASS__;
        $fsext2->to = 'ventas_pedido';
        $fsext2->type = 'button';
        $fsext2->text = '<span class="glyphicon glyphicon-check" aria-hidden="true"></span>'
                . '<span class="hidden-xs">&nbsp; Pagar...</span>';
        $fsext2->params = '&pedido=TRUE';
        $fsext2->save();
    }

    private function agrega_detalle()
    {
        $detalle = new detalle_servicio();
        $detalle->descripcion = 'SMS enviado correctamente al teléfono: ' . $this->telefono . ' con el texto: ' . $this->mensaje;
        $detalle->idservicio = $this->servicio->idservicio;
        $detalle->nick = $this->user->nick;

        if ($detalle->save())
        {
            $this->new_message('Detalle guardados correctamente.');
        }
        else
        {
            $this->new_error_msg('Imposible guardar el detalle.');
        }
    }

}
