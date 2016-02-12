<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2016   Luis Miguel Pérez Romero   luismipr@gmail.com
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
   public $cliente;
   public $documento;
   public $documento_url;
   public $id;
   public $mensaje;
   public $provsms;
   public $telefono;
   
   public function __construct()
   {
      parent::__construct(__CLASS__, 'Enviar SMS', 'ventas', FALSE, FALSE);
   }
   
   protected function private_core()
   {
      $this->provsms = new proveedor_sms();
      $this->documento = FALSE;
      $this->documento_url = $this->url();
      $cliente = new cliente();
      
      $servicio = FALSE;
      if( isset($_REQUEST['servicio']) )
      {
         $serv = new servicio_cliente();
         $servicio = $serv->get($_REQUEST['id']);
         if($servicio)
         {
            $this->cliente = $cliente->get($servicio->codcliente);
            $this->id = $servicio->idservicio;
            $this->documento = 'servicio';
            $this->documento_url = $servicio->url();
         }
      }
      else if( isset($_REQUEST['pedido']) )
      {
         $ped = new pedido_cliente();
         $pedido = $ped->get($_REQUEST['id']);
         if($pedido)
         {
            $this->cliente = $cliente->get($pedido->codcliente);
            $this->id = $pedido->idpedido;
            $this->documento = 'pedido';
            $this->documento_url = $pedido->url();
         }
      }

      if( isset($_POST['enviar']) )
      {
         $provsms = $this->provsms->get($_POST['proveedor']);
         if($provsms)
         {
            $this->telefono = $_POST['telefono'];
            $this->mensaje = $_POST['mensaje'];
            if($this->provsms->enviar_sms($this->telefono, $this->mensaje))
            {
               $this->new_message('Mensaje enviado');
               
               if($servicio)
               {
                  $this->agrega_detalle($servicio);
               }
            }
            else
            {
               $this->new_error_msg('Error al enviar el mensaje');
            }
         }
         else
         {
            $this->new_error_msg('Proveedor de SMS no encontrado.');
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
              . '<span class="hidden-xs">&nbsp; SMS</span>';
      $fsext->params = '&servicio=TRUE';
      $fsext->save();

      $fsext2 = new fs_extension();
      $fsext2->name = 'enviar_sms_pedido';
      $fsext2->from = __CLASS__;
      $fsext2->to = 'ventas_pedido';
      $fsext2->type = 'button';
      $fsext2->text = '<span class="glyphicon glyphicon-phone" aria-hidden="true"></span>'
              . '<span class="hidden-xs">&nbsp; SMS</span>';
      $fsext2->params = '&pedido=TRUE';
      $fsext2->save();
   }

   private function agrega_detalle(&$servicio)
   {
      $detalle = new detalle_servicio();
      $detalle->descripcion = 'SMS enviado correctamente al teléfono: ' . $this->telefono . ' con el texto: ' . $this->mensaje;
      $detalle->idservicio = $servicio->idservicio;
      $detalle->nick = $this->user->nick;

      if( $detalle->save() )
      {
         $this->new_message('Detalle guardados correctamente.');
      }
      else
      {
         $this->new_error_msg('Imposible guardar el detalle.');
      }
   }
}
