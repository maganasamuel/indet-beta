<?php
error_reporting(E_ERROR | E_PARSE);

class arrears_tracker
{
    public $clientController;

    public $client;

    public $db;

    public function __construct()
    {
        session_start();

        /* if (! isset($_SESSION['myusername'])) {
            session_destroy();

            header('refresh:0; url=index.php');
        } */

        require_once('libs/composer/vendor/autoload.php');

        include_once('libs/api/controllers/Client.controller.php');

        require_once('libs/api/classes/database.class.php');

        $this->clientController = new ClientController();

        $this->db = new Database();

        $_POST ? $this->post() : $this->index();
    }

    public function index()
    {
        if (($_GET['action'] ?? '') == 'list-issued-deals') {
            echo $this->listIssuedDeals();

            return;
        }

        if (($_GET['action'] ?? '') == 'show') {
            if (! $this->clientFound(true)) {
                echo json_encode(['error' => 'Arrear not found.']);

                return;
            }

            $deals = json_decode($this->client['deals_data']);

            echo json_encode([
                'client' => $this->client,
                'deal' => $deals[$_GET['key']],
            ]);

            return;
        } ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8">
            <meta http-equiv="X-UA-Compatible" content="IE=edge">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>INDET | Arrears Tracker</title>

            <?php include 'partials/nav_bar.html'; ?>

            <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
            <link rel="stylesheet" href="styles.css">
            <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">

            <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/css/selectize.bootstrap3.min.css" integrity="sha512-MNbWZRRuTPBahfBZBeihNr9vTJJnggW3yw+/wC3Ev1w6Z8ioesQYMS1MtlHgjSOEKBpIlx43GeyLM2QGSIzBDg==" crossorigin="anonymous" referrerpolicy="no-referrer" />
            <script src="https://cdnjs.cloudflare.com/ajax/libs/selectize.js/0.13.3/js/standalone/selectize.min.js" integrity="sha512-pF+DNRwavWMukUv/LyzDyDMn8U2uvqYQdJN0Zvilr6DDo/56xPDZdDoyPDYZRSL4aOKO/FGKXTpzDyQJ8je8Qw==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
        </head>
        <body>
            <div>
                <div class="jumbotron text-center">
                    <h2 class="slide">Arrears Tracker</h2>
                </div>

                <div class="modal fade" id="formModal" tabindex="-1" role="dialog" aria-labelledby="formModalTitle">
                    <div class="modal-dialog" role="document">
                        <form id="arrearForm" class="modal-content">
                            <input type="hidden" id="client_id" name="client_id" />
                            <input type="hidden" id="key" name="key" />
                            <div class="modal-header bg-primary">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color: white;">&times;</span></button>
                                <h4 class="modal-title text-left" id="formModalTitle">Arrears Form</h4>
                            </div>
                            <div class="modal-body">
                                <div id="errorAlert" class="alert alert-danger hidden" role="alert">
                                </div>
                                <div class="form-horizontal">
                                    <div class="form-group">
                                        <label for="client" class="col-sm-3 control-label">Client</label>
                                        <div class="col-sm-9">
                                            <select class="form-control" id="client_id_key"></select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="insurer" class="col-sm-3 control-label">Insurer</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="insurer" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="policy_number" class="col-sm-3 control-label">Policy Number</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" id="policy_number" readonly>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="status" class="col-sm-3 control-label">Status</label>
                                        <div class="col-sm-9">
                                            <select id="arrear_status" name="arrear_status" class="form-control">
                                                <option value="">-</option>
                                                <option value="In Arrears">In Arrears</option>
                                                <option value="Cleared">Cleared</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="notes" class="col-sm-3 control-label">Notes</label>
                                        <div class="col-sm-9">
                                            <textarea id="arrear_notes" name="arrear_notes" class="form-control" style="resize:vertical;" rows="5"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button id="btnSave" type="submit" class="btn btn-primary">Save Arrear</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div id="confirmDeleteModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalTitle">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true" style="color: white;">&times;</span></button>
                                <h4 class="modal-title" id="confirmDeleteModalTitle">Delete Arrear</h4>
                            </div>
                            <div class="modal-body">
                                <strong>Are you sure you want to delete this arrear?</strong>
                            </div>
                            <div class="modal-footer">
                                <button type="button" id="btn-confirm-delete" class="btn btn-danger">Confirm Delete</button>
                                <button type="button" class="btn btn-primary" data-dismiss="modal">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="margined table-responsive">
                    <div class="row">
                        <div class="col-sm-3 col-sm-offset-9 text-center">
                            <button type="button" id="btn-add" data-toggle="modal" data-target="#formModal" class="pull-right btn btn-primary btn-m">
                                <i class="fa fa-plus"></i> Add Policy
                            </button>
                        </div>
                    </div>

                    <br>

                    <div class="row">
                        <div class="col-sm-12">
                            <table id="table" data-toggle="table" class="table table-striped" cellpadding="5px" cellspacing="5px" style="width: 95%;">
                                <thead>
                                    <tr>
                                        <td>Client Name</td>
                                        <td>Insurer</td>
                                        <td>Policy Number</td>
                                        <td>Status</td>
                                        <td>Notes</td>
                                        <td>&nbsp;</td>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($this->listArrearDeals() as $arrear) { ?>
                                        <tr>
                                            <td><?php echo $arrear->client_name; ?></td>
                                            <td class="text-nowrap"><?php echo $arrear->company; ?></td>
                                            <td class="text-nowrap"><?php echo $arrear->policy_number; ?></td>
                                            <td class="text-nowrap"><?php echo $arrear->arrear_status; ?></td>
                                            <td><?php echo $arrear->arrear_notes; ?></td>
                                            <td class="text-nowrap">
                                                <button type="button" class="btn-edit btn btn-warning" data-client_id="<?php echo $arrear->client_id; ?>" data-key="<?php echo $arrear->key; ?>" data-toggle="tooltip" title="Edit"><span class="glyphicon glyphicon-pencil"></span></button>
                                                <button type="button" class="btn-delete btn btn-danger" data-client_id="<?php echo $arrear->client_id; ?>" data-key="<?php echo $arrear->key; ?>" data-toggle="tooltip" title="Delete"><span class="glyphicon glyphicon-trash"></span></button>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <script type="text/javascript">
                var formUpdate = false;
                var client;
                var oldClientId;
                var oldKey;

                $(function(){
                    $('#table').dataTable();

                    $('#btn-add').on('click', function(){
                        formUpdate = false;

                        $('#formModalTitle').text('Add Policy');

                        if(client[0].selectize.items.length){
                            client[0].selectize.removeItem(client[0].selectize.items[0]);
                        }

                        $('input, select, textarea').val('');
                    });

                    $(document).on('click', '.btn-edit', function(){
                        formUpdate = true;
                        oldClientId = $(this).data('client_id');
                        oldKey = $(this).data('key');

                        $('#formModalTitle').text('Edit Policy');

                        $.get('arrears_tracker', {
                            action: 'show',
                            client_id: oldClientId,
                            key: oldKey,
                        }).always(function(response){
                            var response = JSON.parse(response);

                            if(response.error){
                                alert(response.error);

                                return;
                            }

                            $('#client_id').val(oldClientId);
                            $('#key').val(oldKey);

                            var option = {
                                    client_id_key: oldClientId + '-' + oldKey,
                                    client_id: oldClientId,
                                    key: oldKey,
                                    client_name: response.client.name,
                                    company: response.deal.company,
                                    policy_number: response.deal.policy_number
                            };

                            client[0].selectize.addOption(option);
                            client[0].selectize.addItem(option.client_id_key);

                            $('#insurer').val(response.deal.company);
                            $('#policy_number').val(response.deal.policy_number);
                            $('#arrear_status').val(response.deal.arrear_status);
                            $('#arrear_notes').val(response.deal.arrear_notes);

                            $('#formModal').modal('show');
                        });
                    });

                    $(document).on('click', '.btn-delete', function(){
                        oldClientId = $(this).data('client_id');
                        oldKey = $(this).data('key');

                        $('#confirmDeleteModal').modal('show');
                    });

                    $('#btn-confirm-delete').on('click', function(){
                        $.post('arrears_tracker', {
                            action: 'delete',
                            client_id: oldClientId,
                            key: oldKey,
                        }).always(function(response){
                            var response = JSON.parse(response);

                            if(response.error){
                                alert(response.error);

                                return;
                            }

                            $('#confirmDeleteModal').modal('hide');

                            window.location = 'arrears_tracker';
                        });
                    });

                    client = $('#client_id_key').selectize({
                        valueField: 'client_id_key',
                        labelField: 'client_name',
                        searchField: 'client_name',
                        create: false,
                        highlight: false,
                        preload: 'focus',
                        load: function(query, callback){
                            if(!query.length){
                                return callback();
                            }

                            $.get('arrears_tracker', {
                                action: 'list-issued-deals',
                                search: query,
                            }).done(function(response){
                                callback(JSON.parse(response));
                            }).fail(function(){
                                callback();
                            });
                        },
                        render: {
                            item: function(item, escape){
                                return (`
                                    <div class="item"
                                        data-client_id="${escape(item.client_id)}"
                                        data-key="${escape(item.key)}"
                                        data-insurer="${escape(item.company)}"
                                        data-policy-number="${escape(item.policy_number)}"
                                        >
                                        ${escape(item.client_name)}
                                    </div>
                                `);
                            },
                            option: function(item, escape){
                                return (`
                                    <div class="option">
                                        ${escape(item.client_name)}<br>
                                        ${escape(item.company)}<br>
                                        ${escape(item.policy_number)}
                                    </div>
                                `);
                            }
                        },
                        onItemAdd: function(value, item){
                            $('#client_id').val(item.data('client_id'));
                            $('#key').val(item.data('key'));
                            $('#insurer').val(item.data('insurer'));
                            $('#policy_number').val(item.data('policyNumber'));
                        }
                    });

                    $('#arrearForm').submit(function(event){
                        event.preventDefault();

                        $('#errorAlert').addClass('hidden');

                        var data = [];

                        $(this).serializeArray().forEach(function(item){
                            data[item.name] = item.value
                        });

                        var data = Object.assign({}, data);

                        if(formUpdate){
                            data.action = 'update';
                            data.old_client_id = oldClientId;
                            data.old_key = oldKey;
                        }else{
                            data.action = 'create';
                        }

                        $.post('arrears_tracker', data).always(function(response){
                            var response = JSON.parse(response);

                            if(response.error){
                                $('#errorAlert').removeClass('hidden').text(response.error);

                                return;
                            }

                            $('#formModal').modal('hide');

                            window.location = 'arrears_tracker';
                        });
                    });
                });
            </script>
        </body>
        </html>
        <?php
    }

    public function listDeals()
    {
        $issuedPolicies = $this->clientController->getAllIssuedClientProfiles();

        $collection = collect([]);

        while ($issuedPolicy = $issuedPolicies->fetch_assoc()) {
            foreach (json_decode($issuedPolicy['deals_data']) as $key => $deal) {
                $deal->client_id = $issuedPolicy['client_id'];
                $deal->key = $key;
                $deal->client_id_key = $deal->client_id . '-' . $deal->key;
                $deal->client_name = trim($issuedPolicy['name']);

                $deal->notes = '';
                $deal->clawback_notes = '';
                $deal->refund_notes = '';

                $collection->push($deal);
            }
        }

        return $collection
            ->whereNotNull('client_id')
            ->sortBy('client_name');
    }

    public function listIssuedDeals()
    {
        return $this->listDeals()
            ->whereNull('arrear_status')
            ->when($_GET['search'] ?? '', function ($collection) {
                return $collection->filter(function ($item) {
                    return false !== stristr($item->client_name, $_GET['search']);
                });
            })->values()
            ->toJson();
    }

    public function listArrearDeals()
    {
        return $this->listDeals()
            ->whereNotNull('arrear_status')
            ->values()
            ->all();
    }

    public function clientFound($hasArrear = false)
    {
        $client_id = $_POST ? ($_POST['client_id'] ?? '') : ($_GET['client_id'] ?? '');

        $key = $_POST ? ($_POST['key'] ?? '') : ($_GET['key'] ?? '');

        if (! $client_id) {
            return false;
        }

        $this->client = $this->clientController->getIssuedClientProfile($client_id)->fetch_assoc();

        if (! $this->client) {
            return false;
        }

        $deals = json_decode($this->client['deals_data']);

        $deal = $deals[$key] ?? [];

        if (! count($deal)) {
            return false;
        }

        if ($hasArrear && ! $deal->arrear_status) {
            return false;
        } elseif (! $hasArrear && $deal->arrear_status) {
            return false;
        }

        return true;
    }

    public function validForm($update = false)
    {
        unset($_SESSION['error']);

        if ($update) {
            if (! $_POST['old_client_id'] ?? '') {
                $_SESSION['error'] = 'Invalid request.';

                return false;
            }
        }

        if (! $update || ($update && ! ($_POST['old_client_id'] == $_POST['client_id'] && ($_POST['old_key'] ?? '') == $_POST['key']))) {
            if (! $this->clientFound(false)) {
                $_SESSION['error'] = 'Please provide a valid value for Client.';

                return false;
            }
        }

        if (! in_array(($_POST['arrear_status'] ?? ''), ['In Arrears', 'Cleared'])) {
            $_SESSION['error'] = 'Please provide a valid value for Status.';

            return false;
        }

        return true;
    }

    public function post()
    {
        if (! in_array(($_POST['action'] ?? ''), ['create', 'update', 'delete'])) {
            echo json_encode(['error' => 'Invalid request.']);

            return;
        }

        echo $this->{$_POST['action']}();

        return;
    }

    public function create()
    {
        if ($this->validForm()) {
            $this->updateClient('client_id', 'key');

            return json_encode(['error' => '']);
        }

        return json_encode(['error' => $_SESSION['error']]);
    }

    public function update()
    {
        if ($this->validForm(true)) {
            $this->updateClient('old_client_id', 'old_key', true);

            $this->updateClient('client_id', 'key');

            return json_encode(['error' => '']);
        }

        return json_encode(['error' => $_SESSION['error']]);
    }

    public function updateClient($client_id_name, $key_name, $replace = false)
    {
        $this->client = $this->clientController->getIssuedClientProfile($_POST[$client_id_name])->fetch_assoc();

        $deals = json_decode($this->client['deals_data']);

        if ($replace) {
            unset($deals[$_POST[$key_name]]->arrear_notes, $deals[$_POST[$key_name]]->arrear_status);
        } else {
            $deals[$_POST[$key_name]]->arrear_status = $_POST['arrear_status'];
            $deals[$_POST[$key_name]]->arrear_notes = $_POST['arrear_notes'] ?? '';
        }

        $deals = json_encode($deals);

        $sql = 'UPDATE submission_clients SET deals = ? WHERE client_id = ?';

        $query = $this->db->prepare($sql);

        $query->bind_param('si', $deals, $_POST[$client_id_name]);

        $query->execute();
    }

    public function delete()
    {
        if (! $this->clientFound(true)) {
            echo json_encode(['error' => 'Arrear not found.']);

            return;
        }

        $this->updateClient('client_id', 'key', true);

        return json_encode(['error' => '']);
    }
}

new arrears_tracker();
