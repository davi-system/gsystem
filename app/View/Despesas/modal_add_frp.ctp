<?php echo $this->Session->flash(); ?>

<div class="modal fade" id="modalAddFrp" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Adicionar nova forma de pagamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="card">
                    <div class="card-header text-white bg-primary"><i class="bi bi-pencil-fill"></i> <b>Forma de Pagamento</b></div>
                
                    <div class="card-body">                                        
                        <div class="row">
                            <div class="col-md-12">                                                                            
                                <?php 
                                    echo $this->Form->input('frp_descricao', array(
                                        'label' => 'Descrição',
                                        'type' => 'text',
                                        'class' => 'form-control',
                                        'id' => 'descricao',
                                        'placeholder' => 'Informe descrição'
                                    )); 
                                ?>                        
                            </div>                    
                        </div>                       
                    </div>                                                 
                </div>                                

                <br />

                <div class="card">
                    <div class="card-header text-white" style="background-color: purple;"><b>Forama de Pagamento</b></div>
                
                    <div class="card-body">                                                                                                           
                        <div class="row">
                            <div class="col-md-12">
                                <table class="table table-striped table-hover">
                                    <thead>
                                        <tr>
                                            <th>Ações</th>
                                            <th>Forma de Pagamento</th>
                                        </tr>
                                    </thead>
            
                                    <tbody>
                                        <?php 
                                            foreach($formaPagamento as $value) {                                        
                                                echo "
                                                    <tr>
                                                        <td>
                                                            ".
                                                                $this->Form->button('<i class="bi bi-pencil-square"></i>', array(
                                                                    'title' => 'Editar forma de pagamento',
                                                                    'type' => 'button',                                                        
                                                                    'onclick' => "abreModalEditFrp({$value['frp']['frp_id']});",
                                                                    'class' => 'btn btn-success'                                                                                                                         
                                                                ))
                                                            ."
        
                                                            ".
                                                                $this->Form->button('<i class="bi bi-trash"></i>', array(
                                                                    'title' => 'Exluir tipo',
                                                                    'type' => 'button',                                                        
                                                                    'onclick' => "excluirFormaPagamento({$value['frp']['frp_id']});",
                                                                    'class' => 'btn btn-danger'                                                                                                                         
                                                                ))
                                                            ."
                                                        </td>
                                                        <td>{$value['frp']['frp_descricao']}</td>
                                                    </tr>                                
                                                ";
                                            }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer bg-transparent" style="text-align: right;">                     
                        <?php 
                            echo $this->Form->button('Salvar', array(
                                'title' => 'Salvar cadastro',
                                'type' => 'button',
                                'class' => 'btn btn-primary',
                                'onclick' => 'saveNovaFormaDePagamento();'
                            )); 
                        ?>
            
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Fechar</button>
                    </div>
                </div> 
            </div>                 
        </div>
    </div>
</div>

<script>

    function saveNovaFormaDePagamento() {   
        
        if($('#descricao').val() == '') {
            alert('Descrição não pode ficar em branco!');
        } else {

            $.ajax({
                url: `<?php echo $this->Html->url(array('controller' => 'Despesas', 'action' => 'salvarFormaPagamento')); ?>`,
                type: "POST",            
                data: {                
                    'descricao': $('#descricao').val()                
                }
            }).done((data) => {            
                swal({
                    title: "Sucesso!",
                    text: "Nova forma de pagamento cadastrada com sucesso!",
                    icon: "success",
                    button: false
                });
                setTimeout((data) => {
                    $(window.location.reload()).hide();
                }, 2000);   
            });
        }
    }

    function excluirFormaPagamento(frpId) {
        
        if(confirm('Deseja realmente excluir?') == true) {
            $.ajax({
                url: `<?php echo $this->Html->url(array('controller' => 'Despesas', 'action' => 'deletaFormaPagamento')); ?>/${frpId}`,
                type: "GET"    
            }).done((data) => {                         
                swal({
                    title: "Sucesso!",
                    text: "Forma de pagamento excluido com sucesso!",
                    icon: "success",
                    // button: false
                });

                setTimeout((data) => {
                    $('#modalAddFrp').modal('hide');
                    abreModalAddFrp();
                }, 2000);
            });
        }
    }

    function abreModalEditFrp(idFrp) {
        
        $.ajax({
            url: `<?php echo $this->Html->url(array('controller' => 'Despesas', 'action' => 'modalEditFrp')); ?>/${idFrp}`,
            type: 'GET'            
        }).done((data) => { 
            $('#modalAddFrp').modal('hide');
            $('#modal').html(data);
            $('#modalEditFrp').modal('show');
        });
    }

</script>