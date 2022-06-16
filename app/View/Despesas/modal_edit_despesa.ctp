<?php echo $this->Session->flash(); ?>

<div class="modal fade" id="modalEditDespesa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Editar Despesa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">

                        <div class="row">
                            <div class="col-md-12">
                                <?php
                                    echo $this->Form->input('des_descricao', array(
                                        'label' => 'Descrição',
                                        'type' => 'text',
                                        'class' => 'form-control',
                                        'value' => $despesa['des']['des_descricao'],
                                        'id' => 'descricao'
                                    ));
                                ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-3">
                                <?php
                                    echo $this->Form->input('des_valor', array(
                                        'label' => 'Valor',
                                        'type' => 'number',
                                        'class' => 'form-control',
                                        'value' => $despesa['des']['des_valor'],
                                        'id' => 'valor'
                                    ));
                                ?>
                            </div>

                            <div class="col-md-3">
                                <?php
                                    echo $this->Form->input('des_tipo_fk', array(
                                        'label' => 'Tipo',
                                        'type' => 'select',
                                        'options' => $tipos,
                                        'empty' => true,
                                        'class' => 'form-control',
                                        'value' => $despesa['des']['des_tipo_fk'],
                                        'id' => 'tipo'
                                    ));
                                ?>
                            </div>

                            <div class="col-md-3">
                                <?php
                                    echo $this->Form->input('des_frp_fk', array(
                                        'label' => 'Forma de Pagamento',
                                        'type' => 'select',
                                        'options' => $formaPagamento,
                                        'empty' => true,
                                        'class' => 'form-control',
                                        'value' => $despesa['des']['des_frp_fk'],
                                        'id' => 'frp'
                                    ));
                                ?>
                            </div>

                            <div class="col-md-3">
                                <?php
                                    echo $this->Form->input('des_parcela', array(
                                        'label' => 'Parcela',
                                        'type' => 'number',
                                        'class' => 'form-control',
                                        'value' => $despesa['des']['des_parcela'],
                                        'id' => 'parcela'
                                    ));
                                ?>
                            </div>
                        </div>

                        <br />

                        <div class="col-auto">
                            <?php
                                echo $this->Form->button('Salvar', array(
                                    'title' => 'Salvar cadastro de despesa',
                                    'type' => 'button',
                                    'class' => 'btn btn-primary',
                                    'onclick' => "saveEditDespesa({$id});"
                                ));
                            ?>
                        </div>

                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>

<script>

    function saveEditDespesa(id) {        

        $.ajax({
            url: `<?php echo $this->Html->url(array('controller' => 'Despesas', 'action' => 'salvaEditDespesa')); ?>`,
            type: "POST",
            contentType: "application/x-www-form-urlencoded; charset=UTF-8",
            data: {
                'id': id,
                'descricao': $('#descricao').val(),
                'valor': $('#valor').val(),
                'tipo': $('#tipo').val(),
                'formaPagamento': $('#frp').val(),
                'parcelas': $('#parcela').val()
            }
        }).done((data) => {            
            swal({
                    title: "Sucesso!",
                    text: "Registro editado com sucesso!",
                    icon: "success",
                    button: false
                });
                setTimeout((data) => {
                $(window.location.reload()).hide();
            }, 2000);   
        });
    }

</script>