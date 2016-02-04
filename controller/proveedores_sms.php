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

/**
 * Description of proveedor_sms
 *
 * @author luismi
 */
class proveedores_sms extends fs_controller
{

    public $allow_delete;
    public $proveedor_sms;
    public $resultados;

    public function __construct()
    {
        parent::__construct(__CLASS__, 'SMS', 'admin');
    }

    protected function private_core()
    {

        /// ¿El usuario tiene permiso para eliminar en esta página?
        $this->allow_delete = $this->user->allow_delete_on(__CLASS__);

        $proveedor = new proveedor_sms();
        $this->resultados = array();

        if (!ini_get('allow_url_fopen'))
        {
            $this->new_error_msg('tienes que activar allow_url_fopen en el php.ini. Consulta con tu ISP ');
        }

        //nuevo proveedor:
        if (isset($_POST['nuevo']))
        {
            $proveedor->descripcion = $_POST['descripcion'];
            $proveedor->url = $_POST['url'];
            $proveedor->usuario = $_POST['usuario'];
            $proveedor->password = $_POST['password'];
            $proveedor->de = $_POST['de'];

            if (isset($_POST['api_id']))
            {
                $proveedor->api_id = $_POST['api_id'];
            }

            if ($proveedor->save())
            {
                $this->new_message('Proveedor creado correctamente.');
            }
            else
            {
                $this->new_error_msg('Error al guardar el proveedor.');
            }
            $this->resultados = $proveedor->all();
        }
        else if (isset($_REQUEST['idproveedor']))
        {
            $this->proveedor_sms = $proveedor->get($_REQUEST['idproveedor']);
            $this->template = 'editar_proveedor_sms';
            if (isset($_POST['usuario']))
            {
                $this->proveedor_sms->usuario = $_POST['usuario'];
                $this->proveedor_sms->password = $_POST['password'];
                $this->proveedor_sms->de = $_POST['de'];

                if (isset($_POST['api_id']))
                {
                    $this->proveedor_sms->api_id = $_POST['api_id'];
                }
                if ($this->proveedor_sms->save())
                {
                    $this->new_message('Proveedor guardado correctamente.');
                }
                else
                {
                    $this->new_error_msg('Error al guardar el proveedor.');
                }
            }
        }
        else if (isset($_GET['delete']))
        {
            $prov = $proveedor->get($_GET['delete']);
            if ($prov)
            {
                if ($prov->delete())
                {
                    $this->new_message('Proveedor eliminado correctamente.');
                }
                else
                {
                    $this->new_error_msg('Imposible eliminar el proveedor.');
                }
            }

            $this->resultados = $proveedor->all();
        }

        $this->resultados = $proveedor->all();
    }

}
