<?php

/*
 * This file is part of FacturaSctipts
 * Copyright (C) 2016  Luis Miguel Pérez Romero  luismipr@gmail.com
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
 */

/**
 * Description of proveedor_sms
 *
 * @author luismi
 */
class proveedor_sms extends fs_model
{
   public $idproveedor;
   public $url;
   public $descripcion;
   public $usuario;
   public $password;
   public $de;
   public $api_id;

   public function __construct($s = FALSE)
   {
      parent::__construct('proveedor_sms');
      if($s)
      {
         $this->idproveedor = $s['idproveedor'];
         $this->url = $s['url'];
         $this->descripcion = $s['descripcion'];
         $this->usuario = $s['usuario'];
         $this->password = $s['password'];
         $this->de = $s['de'];
         $this->api_id = $s['api_id'];
      }
      else
      {
         $this->idproveedor = NULL;
         $this->url = NULL;
         $this->descripcion = NULL;
         $this->usuario = NULL;
         $this->password = NULL;
         $this->de = NULL;
         $this->api_id = NULL;
      }
   }

   protected function install()
   {
      return '';
   }

   public function get($idproveedor)
   {
      $data = $this->db->select("SELECT * FROM proveedor_sms WHERE idproveedor = " . $this->var2str($idproveedor) . ";");
      if($data)
      {
         return new proveedor_sms($data[0]);
      }
      else
         return FALSE;
   }

   public function url()
   {
      return 'index.php?page=proveedores_sms&idproveedor=' . $this->idproveedor;
   }

   public function exists()
   {
      if( is_null($this->idproveedor) )
      {
         return FALSE;
      }
      else
         return $this->db->select("SELECT * FROM proveedor_sms WHERE idproveedor = " . $this->var2str($this->idproveedor) . ";");
   }

   public function save()
   {
      if( $this->exists() )
      {
         $sql = "UPDATE proveedor_sms SET "
                 . "url = " . $this->var2str($this->url) .
                 ", descripcion = " . $this->var2str($this->descripcion) .
                 ", usuario = " . $this->var2str($this->usuario) .
                 ", password = " . $this->var2str($this->password) .
                 ", de = " . $this->var2str($this->de) .
                 ", api_id = " . $this->var2str($this->api_id) . ""
                 . "WHERE idproveedor = " . $this->var2str($this->idproveedor) . ";";

         return $this->db->exec($sql);
      }
      else
      {
         $sql = "INSERT INTO proveedor_sms (url,descripcion,usuario,password,de,api_id) VALUES
                 (" . $this->var2str($this->url) .
                 "," . $this->var2str($this->descripcion) .
                 "," . $this->var2str($this->usuario) .
                 "," . $this->var2str($this->password) .
                 "," . $this->var2str($this->de) .
                 "," . $this->var2str($this->api_id) . ");";

         if( $this->db->exec($sql) )
         {
            $this->idproveedor = $this->db->lastval();
            return TRUE;
         }
         else
            return FALSE;
      }
   }

   public function delete()
   {
      return $this->db->exec("DELETE FROM proveedor_sms WHERE idproveedor = " . $this->var2str($this->idproveedor));
   }

   public function all()
   {
      $s = array();
      
      $data = $this->db->select("SELECT * FROM proveedor_sms ORDER BY idproveedor ASC;");
      if($data)
      {
         foreach($data as $d)
         {
            $s[] = new proveedor_sms($d);
         }
      }
      
      return $s;
   }   
}
