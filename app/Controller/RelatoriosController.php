<?php 

App::uses('Controller', 'Controller');

class RelatoriosController extends AppController {

    public $components = array("Utilitarios");

    var $uses = array('Despesas');    

    public function index()
    {
        $usuario = $this->Session->read('Person.usuario');

        if($this->request->is('post')) {            

            $data1 = $this->request->data['Rel']['data1'];
            $data2 = $this->request->data['Rel']['data2'];                        
    
            $this->set('despesas', $this->Despesas->relatorioDespesas(
                $usuario['usu']['usu_id'], 
                $this->Utilitarios->formataData($data1), 
                $this->Utilitarios->formataData($data2)
            ));            
        }
        
    }

    public function r01Excel()
    {
        # code...
    }
}