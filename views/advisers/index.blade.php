<html>

<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="_token" content="{{ $token }}">
  <link href="https://fonts.googleapis.com/css?family=Roboto" rel="stylesheet">
  <link rel="stylesheet" href="styles.css">
  <link id="favicon" rel="icon" href="Logo_ImageOnly.png" type="image/png" sizes="16x16">
  <title>INDET | Advisers</title>

  <style>
    .td-notes {
      width: 100%;
      text-align: left;
    }

    .td-action {
      width: 0%;
      white-space: nowrap;
      vertical-align: top !important;
    }

  </style>

  <script>
    var table = null;
  </script>
  <script src="js/date_helper.js"></script>
  <script src="js/advisers-crud.js"></script>
  <script>
    window.addEventListener('load', function() {
      $(function() {
        $('body').tooltip({
          selector: '[rel=tooltip]'
        });

        $('.datepicker').datepicker({
          dateFormat: 'dd/mm/yy'
        });

        $('#advisers_table').dataTable({
          "columns": [{}, {}, {}, {}, {}, {}, {},
            {
              render: function(data, type, row) {
                return '<input data-toggle="modal" data-target="#myModal" type="image" class="open-modal" src="edit.png" data-toggle="tooltip" title="Edit Adviser Profile" value="' +
                  row[0] + '">';
              }
            },
            {
              render: function(data, type, row) {
                return `
                  <a href="adviser_profile.php?id=` + row[0] + `" class="btn btn-primary" data-toggle="tooltip" title="View Adviser Profile">
                      <i class="fas fa-search"></i>
                  </a>
                  &nbsp;
                  <a href="adviser_strings.php?adviser_id=` + row[0] + `">View Strings</i></a>
                `;
              }
            },
          ],
          "order": [
            [1, "asc"]
          ],
          "columnDefs": [{
              "targets": [2, 3],
              "orderable": true
            },
            {
              "targets": [0],
              "visible": false,
              "searchable": false,
            }
          ]
        });

        table = $("#advisers_table").DataTable();

        var counter = 1;
      });
    });
  </script>
</head>

<body>
  @php include 'partials/nav_bar.html'; @endphp
  <div align="center">
    <div class="jumbotron">
      <h2 class="slide">Adviser Profiles</h2>
    </div>

    <div class="margined table-responsive">
      <div class="row">
        <div class="col-sm-9 text-center"></div>
        <div class="col-sm-3 text-center"><button id="btn-add" name="btn-add" data-toggle="modal" data-target="#myModal" class="pull-right btn btn-primary btn-m"><i class="fa fa-plus"></i> Add New
            Adviser</button></div>
      </div>
      <br>
      <table id='advisers_table' data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" width='95%' style=" display: block; overflow-x: auto; white-space: nowrap;">
        <thead>
          <td>&nbsp;</td>
          <td>Adviser Name</td>
          <td>Adviser FSP number</td>
          <td>Adviser Address</td>
          <!--td>IRD number</td-->
          <td>Email Address</td>
          <td>Leads Charge</td>
          <td>Issued Charge</td>
          {{-- <td>
            <a id="deleteall" class="a" href="delete_adviser.php?del_id=all">
                <img src="delete.png" />
            </a>
          </td> --}}
          <td></td>
          <td></td>
        </thead>

        <tbody id="advisers-list">
          @while ($rows = $advisers->fetch_assoc())
            <tr id="adviser{{ $rows['id'] }}" cellpadding='5px' cellspacing='5px'>
              <td>{{ $rows['id'] }}</td>
              <td>{{ $rows['name'] }}</td>
              <td>{{ $rows['fsp_num'] }}</td>
              <td>{{ $rows['address'] }}</td>
              <td>{{ $rows['email'] }}</td>
              <td>{{ $rows['leads'] }}</td>
              <td>{{ $rows['bonus'] }}</td>
              <td></td>
              <td></td>
            </tr>
          @endwhile
        </tbody>
      </table>
    </div>

    <!-- Modals Editor -->
    <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header" style="background-color: #286090; ">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span style="color:white;" aria-hidden="true">×</span></button>
            <h4 class="modal-title" id="myModalLabel" style="color:white;">Adviser Editor</h4>
          </div>
          <div class="modal-body">
            <form id="frmAdviser" name="frmAdviser" class="form-horizontal" novalidate="">
              <div class="form-group error">
                <div class="col-sm-8">
                  <div class="row">
                    <div class="col">

                      <div class="row" style="margin-top: 10px !important;">
                        <div class="col">
                          <label for="inputTask" class="col-sm-4 control-label">Position</label>
                          <div class="col-sm-8">
                            <select id="position" class="form-control" name="position_id" required>
                              <option value="0" selected>--Select Position--</option>

                              @php
                                $query = 'SELECT * from positions ORDER BY name ASC';
                                ($displayquery = mysqli_query($con, $query)) or die('Could not look up user information; ' . mysqli_error($con));
                              @endphp

                              @while ($rows = mysqli_fetch_array($displayquery))
                                @if ($rows['name'] != 'EliteInsure Team')
                                  <option value="{{ $rows['id'] }}">{{ $rows['name'] }}</option>
                                @endif
                              @endwhile
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="row" style="margin-top: 10px !important;">
                        <div class="col">
                          <label for="inputTask" class="col-sm-4 control-label">Name</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control has-error" id="name" name="name" placeholder="Name" value="" required>
                          </div>
                        </div>
                      </div>

                      <div class="row" style="margin-top: 10px !important;">
                        <div class="col">
                          <label for="inputTask" class="col-sm-4 control-label">Company Name</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control has-error" id="company_name" name="company_name" placeholder="Company Name" value="" required>
                          </div>
                        </div>
                      </div>

                      <div class="row" style="margin-top: 10px !important;">
                        <div class="col">
                          <label for="inputTask" class="col-sm-4 control-label">Payroll Name</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control has-error" id="payroll_name" name="payroll_name" placeholder="Payroll Name" value="" required>
                          </div>
                        </div>
                      </div>

                      <div class="row" style="margin-top: 10px !important;">
                        <div class="col">
                          <label for="inputTask" class="col-sm-4 control-label">Team (ADR)</label>
                          <div class="col-sm-8">
                            <select id="team" class="form-control" name="team_id" required>
                              <option value="0" selected>None</option>

                              @php
                                $query = 'SELECT * from teams ORDER BY name ASC';
                                ($displayquery = mysqli_query($con, $query)) or die('Could not look up user information; ' . mysqli_error($con));
                              @endphp

                              @while ($rows = mysqli_fetch_array($displayquery))
                                @if ($rows['name'] != 'EliteInsure Team')
                                  <option value="{{ $rows['id'] }}">{{ $rows['name'] }}</option>
                                @endif
                              @endwhile
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="row" style="margin-top: 10px !important;">
                        <div class="col">
                          <label for="inputTask" class="col-sm-4 control-label">Team (SADR)</label>
                          <div class="col-sm-8">
                            <select id="steam" class="form-control" name="steam_id" required>
                              <option value="0" selected>None</option>

                              @php
                                $query = 'SELECT * from steams ORDER BY name ASC';
                                ($displayquery = mysqli_query($con, $query)) or die('Could not look up user information; ' . mysqli_error($con));
                              @endphp

                              @while ($rows = mysqli_fetch_array($displayquery))
                                @if ($name != 'EliteInsure Team')
                                  <option value="{{ $rows['id'] }}">{{ $rows['name'] }}</option>
                                @endif
                              @endwhile
                            </select>
                          </div>
                        </div>
                      </div>

                      <div class="row" style="margin-top: 10px !important;">
                        <div class="col">
                          <label for="inputTask" class="col-sm-4 control-label">FSP Number</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control has-error" id="fsp_num" name="fsp_num" placeholder="FSP Number" value="" required>
                          </div>
                        </div>
                      </div>

                      <div class="row" style="margin-top: 10px !important;">
                        <div class="col">
                          <label for="inputTask" class="col-sm-4 control-label">Birthday</label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control has-error datepicker" id="birthday" name="birthday" placeholder="Birthday" value="" required>
                          </div>
                        </div>
                      </div>


                      <div class="row" style="margin-top: 10px !important;">
                        <div class="col">
                          <label for="inputTask" class="col-sm-4 control-label">Email Address </label>
                          <div class="col-sm-8">
                            <input type="text" class="form-control has-error" id="email" name="email" placeholder="Email Address" value="" required>
                          </div>
                        </div>
                      </div>


                    </div>
                  </div>
                </div>
                <div class="col-sm-4">
                  <div class="row">
                    <div class="col">
                      <img alt="..." class="img-thumbnail" id="imgPreview" style="width: auto; object-fit: contain;" />
                    </div>
                  </div>
                  <div class="row">
                    <div class="col"><input type="file" class="form-control has-error" id="imageInput" name="imageInput" value="" required>
                      <div class="col"><input type="hidden" class="form-control has-error" id="image" name="image" value="" required>
                      </div>
                    </div>
                  </div>
                </div>
              </div>


              <div class="form-group error row" style=" margin-top:10px !important;">
                <label for="inputTask" class="col-sm-2 control-label">Address</label>
                <div class="col-sm-10">
                  <textarea class="form-control has-error" id="address" name="address" placeholder="Address" required></textarea>
                </div>
              </div>

              <div class="form-group error row" style="margin-top: 10px !important;">
                <label for="inputTask" class="col-sm-2 control-label">Leads</label>
                <div class="col-sm-4">
                  <input type="text" class="form-control has-error" id="leads" name="leads" placeholder="Leads" value="">
                </div>
                <label for="inputTask" class="col-sm-2 control-label">Issue Charge</label>
                <div class="col-sm-4">
                  <input type="text" class="form-control has-error" id="bonus" name="bonus" placeholder="Issue Charge" value="">
                </div>
              </div>
              <!--
                                <div class="form-group error">
                                    <label for="inputTask" class="col-sm-12"><strong><h3 class="text-center">API Integration</h3></strong></label>
                                </div>

                                <div class="form-group error">
                                    <label for="inputTask" class="col-sm-6 control-label">Adviser's Name in Payroll Software</label>
                                    <div class="col-sm-4">
                                        <input type="text" class="form-control has-error" id="payroll_name" name="payroll_name" placeholder="Name" value="">
                                    </div>
                                    <label for="inputTask" class="col-sm-2 control-label"></label>
                                    <div class="col-sm-4">
                                    </div>
                                </div>
                            -->

              <div class="form-group error row" style="margin-top: 10px !important;">
                <label for="inputTask" class="col-sm-2 control-label">Date Hired</label>
                <div class="col-sm-4">
                  <input type="text" class="form-control has-error datepicker" id="date_hired" name="date_hired" placeholder="Date Hired" value="" required>
                </div>
                <label for="inputTask" class="col-sm-2 control-label">Date Terminated</label>
                <div class="col-sm-4">
                  <input type="text" class="form-control has-error datepicker" id="termination_date" name="termination_date" placeholder="Date Terminated" value="" required>
                </div>
              </div>

              <input type="hidden" id="adviser_id" name="adviser_id" value="0">
              <input type="hidden" id="formtype" name="formtype" value="0">
              <input type="hidden" id="action" name="action" value="0">
            </form>

            <div id="notesWrapper" class="row hidden" style="margin-top: 1rem;">
              <div class="col-sm-12">
                <hr>
                <form id="notesForm" class="form-inline" style="margin-top: 0;">
                  <input type="hidden" id="notes_adviser_id" name="adviser_id" />
                  <div class="row">
                    <div class="form-group col-sm-9">
                      <textarea id="notes" name="notes" class="form-control" rows="5" placeholder="Notes..." required="true" style="width: 100%;"></textarea>
                    </div>
                    <div class="col-sm-3 text-left">
                      <button type="submit" class="btn btn-primary" style="vertical-align: top;">Add Notes</button>
                    </div>
                  </div>
                </form>
                <div>
                  <table id="notesTable" data-toggle="table" class="table table-striped " cellpadding="5px" cellspacing="5px" style="width: 95%;">
                    <thead>
                      <tr>
                        <td>ID</td>
                        <td>Notes</td>
                        <td>&nbsp;</td>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="btn-save" value="add"><i id="save_spinner" class="fas fa-spinner fa-spin" style="display:none;"></i> Save</button>
          </div>
        </div>
      </div>
    </div>
    <!-- End of Editor -->

    <!-- Confirm Delete -->
    <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
            <h4 class="modal-title" id="myModalLabel">Confirm Deletion</h4>
          </div>
          <form id="frmDelUser" name="frmDelUser" class="form-horizontal" novalidate="">
            <div class="modal-body">
              <div class="form-group error">
                <label for="inputTask" class="col-sm-12 control-label">Are you sure you want to delete this User?
                </label>
              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-danger" id="btn-delete-confirm" value="Yes">Confirm Delete</button>
              <button type="button" class="btn btn-primary" id="btn-delete-cancel" value="No">Cancel</button>
              <input name="_method" id="_method" type="hidden" value="delete" />
              <input type="hidden" id="delete-adviser" value="0">
            </div>
          </form>
        </div>
      </div>
    </div>
    <!-- End of Confirm Delete -->

    <div class="modal fade child-modal" id="updateNotesModal" tabindex="-1" role="dialog" aria-labelledby="updateNotesModalTitle">
      <div class="modal-dialog" role="document">
        <form id="updateNotesForm" class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="updateNotesModalTitle">Update Notes</h4>
          </div>
          <div class="modal-body text-left">
            <input type="hidden" id="update_id" name="id" />
            <input type="hidden" id="update_adviser_id" name="adviser_id" />
            <div class="form-group" style="margin-bottom: 0;">
              <textarea id="update_notes" name="notes" class="form-control" rows="5" placeholder="Notes..." required="true" style="width: 100%;"></textarea>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">Update</button>
          </div>
        </form>
      </div>
    </div>

    <div class="modal fade child-modal" id="deleteNotesModal" tabindex="-1" role="dialog" aria-labelledby="deleteNotesModalTitle">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="deleteNotesModalTitle">Delete Notes</h4>
          </div>
          <div class="modal-body text-left">
            <input type="hidden" id="delete_id" name="id" />
            <div>Are you sure to delete this note?</div>
          </div>
          <div class="modal-footer">
            <button type="button" id="btnDeleteNotes" class="btn btn-primary">Yes</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
          </div>
        </div>
      </div>
    </div>

    @include('advisers.script')
  </div>
</body>

</html>
