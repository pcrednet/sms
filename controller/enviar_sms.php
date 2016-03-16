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
      $this->provsms = new proveedor_sms;
      $this->documento = FALSE;
      $this->telefono = '';
      $this->mensaje = '';

      $this->documento_url = $this->url();
      $cliente = new cliente();

      $servicio = FALSE;
      if (isset($_REQUEST['servicio']))
      {
         $serv = new servicio_cliente();
         $servicio = $serv->get($_REQUEST['id']);
         if ($servicio)
         {
            $this->cliente = $cliente->get($servicio->codcliente);
            $this->id = $servicio->idservicio;
            $this->documento = 'servicio';
            $this->documento_url = $servicio->url();
         }
      }
      else if (isset($_REQUEST['pedido']))
      {
         $ped = new pedido_cliente();
         $pedido = $ped->get($_REQUEST['id']);
         if ($pedido)
         {
            $this->cliente = $cliente->get($pedido->codcliente);
            $this->id = $pedido->idpedido;
            $this->documento = 'pedido';
            $this->documento_url = $pedido->url();
         }
      }

      if (isset($_POST['enviar']))
      {
         $this->provsms = FALSE;
         $provsms = new proveedor_sms();
         $this->provsms = $provsms->get($_POST['proveedor']);
         if ($this->provsms)
         {
            if (!$this->telefono = $_POST['telefono'] == '+34')
            {
               $this->telefono = $_POST['telefono'];
               $this->mensaje = $_POST['mensaje'];
               if (strpos($this->provsms->url, 'freevoipdeal'))
               {
                  if ($this->sms_freevoipdeal())
                  {
                     $this->new_message('Mensaje desde Freevoipdeal enviado');
                     if ($servicio)
                     {
                        $this->agrega_detalle($servicio);
                     }
                     else if ($pedido)
                     {
                        $this->pedido_observaciones();
                     }
                  }
                  else
                  {
                     $this->new_error_msg('Error al enviar el mensaje');
                  }
               }
               else if (strpos($this->provsms->url, 'clickatell'))
               {
                  if ($this->sms_clickatell())
                  {
                     $this->new_message('Mensaje desde Clickatell enviado');
                     if ($servicio)
                     {
                        $this->agrega_detalle($servicio);
                     }
                  }
                  else
                  {
                     $this->new_error_msg('Error al enviar el mensaje');
                  }
               }
            }
            else
            {
               $this->new_error_msg('No has introducido ningún número.');
            }
         }
         else
         {
            $this->new_error_msg('Proveedor de SMS no encontrado.');
         }
      }
   }

   private function agrega_detalle(&$servicio)
   {
      $detalle = new detalle_servicio();
      $detalle->descripcion = 'SMS enviado correctamente al teléfono: ' . $this->telefono . ' con el texto: ' . $this->mensaje;
      $detalle->idservicio = $servicio->idservicio;
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

   public function sms_freevoipdeal()
   {
      //para test comentar la otra url
      //$url = 'http://localhost/facturascripts/plugins/sms/test_sms';

      $url = $this->provsms->url . "?username=" . $this->provsms->usuario
              . "&password=" . $this->provsms->password
              . "&from=" . $this->provsms->de
              . "&to=" . $this->telefono
              . "&text=" . rawurlencode($this->mensaje);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

      $result = curl_exec($ch);
      curl_close($ch);


      if ($result)
      {
         if (strpos($result, 'success'))
         {
            return true;
            $this->new_message($result);
         }
         else
         {
            $this->new_error_msg('<br />' . $url);
         }
      }
      else
      {
         return FALSE;
      }
   }

   public function sms_clickatell()
   {
      $url = $this->provsms->url . "?user=" . $this->provsms->usuario
              . "&password=" . $this->provsms->password
              . "&api_id=" . $this->provsms->api_id
              . "&to=" . $this->telefono
              . "&text=" . rawurlencode($this->mensaje);

      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13");
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

      $result = curl_exec($ch);
      curl_close($ch);

      if ($result)
      {
         if (strpos($result, 'ID'))
         {
            return true;
            $this->new_message($result);
         }
         else
         {
            $this->new_error_msg('<br />' . $url);
         }
      }
      else
      {
         return FALSE;
      }
   }

   public function pedido_observaciones()
   {
      $ped = new pedido_cliente();
      $pedido = $ped->get($_REQUEST['id']);

      $detalle = $pedido->observaciones . 'SMS enviado correctamente al teléfono: ' . $this->telefono . ' con el texto: ' . $this->mensaje . '. ';
      $sql = $this->db->exec("UPDATE pedidoscli SET observaciones = '" . $detalle . "' WHERE idpedido = " . $this->id . ";");
      if ($sql)
      {
         $this->new_message('Observaciones guardadas correctamente.');
      }
   }

}
