<?php 

App::uses('Controller', 'Controller');

class UsuariosController extends AppController {

    var $uses = array('Usuarios');

    public function modalAddUsuario()
    {
        $this->layout = null;
    }

    public function verificaUsuarioExiste()
    {
        $this->layout = null;
        $this->autoRender = false;

        $verificaEmailExiste = $this->Usuarios->find('count', array(
            'conditions' => array(
                'usu_email' => $this->request->data['email']
            )
        ));

        return json_encode($verificaEmailExiste);
    }

    public function saveAddUsuario()
    {
        $this->layout = null;
        $this->autoRender = false;

        $usu = array();
        $usu['usu_nome'] = $this->request->data['nome'];
        $usu['usu_email'] = $this->request->data['email'];
        $usu['usu_senha'] = $this->request->data['senha'];
        $usu['usu_adm'] = 'N';
        $usu['usu_dtcriacao'] = date('Y-m-d');
        $usu['usu_horacriacao'] = date('H:i:s');

        $this->Usuarios->create();
        $this->Usuarios->save($usu);

        $this->Session->write('idUsuario.add', $this->Usuarios->id);        
    }

    public function modalViewUsuario()
    {
        $this->layout = null;

        $usuario = $this->Usuarios->find('first', array(
            'conditions' => array(
                'usu_id' => $this->request->data['id']
            )
        ));
        $this->set('usuario', $usuario);
    }

    public function modalEditUsuario()
    {       
        $this->layout = null;

        $usuario = $this->Usuarios->find('first', array(
            'conditions' => array(
                'usu_id' => $this->request->data['id']
            )
        ));
        $this->set('usuario', $usuario); 
    }

    public function salvaEditCadUsuario()
    {
        $this->layout = null;
        $this->autoRender = false;        

        $usu = array();
        $usu['usu_id'] = $this->request->data['id'];
        $usu['usu_nome'] = $this->request->data['nome'];
        $usu['usu_email'] = $this->request->data['email'];
        $usu['usu_senha'] = $this->request->data['senha'];
        $usu['usu_dtalteracao'] = date('Y-m-d');
        $usu['usu_horaalteracao'] = date('H:i:s');

        $this->Usuarios->save($usu);
    }
}