<!DOCTYPE html>
<html>
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" />
    <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>

    <link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    {{-- <script src="https://code.jquery.com/jquery-1.12.4.js"></script> --}}
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>

</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">Event Table</h2>



      <div class="modal fade" id="showEventModal" tabindex="-1" role="dialog" aria-labelledby="showEventModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
          <div class="modal-content">
            <div class="modal-header">
              <h5 class="modal-title" id="showEventModalLabel">Show Event #<p id="show_id"></p></h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
              </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="required" for="show_name">Name</label>
                    <input type="text" class="form-control show_name" id="show_name" disabled>
                </div>

                <div class="form-group">
                    <label class="required" for="show_slug">Slug</label>
                    <input type="text" class="form-control show_slug" id="show_slug" disabled>
                </div>
                <div class="input-group date " data-provide="datepicker" style="gap: 20px">
                    <div class="flex">
                        <label class="required" for="show_start">Start At</label>
                        <input type="text" class="form-control datepicker-start" id="show_start" disabled>
                    </div>
                    <div class="flex">
                        <label class="required" for="end">End At</label>
                        <input type="text" class="form-control datepicker-end" id="show_end" disabled>
                    </div>
                </div><br>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
              {{-- <button type="button" class="btn btn-primary" id="btn-create">Save</button> --}}
            </div>
          </div>
        </div>
      </div>

      <a type="button" class="btn btn-primary" href="{{ url('events/create') }}" style="margin: 0px 0 24px 0">
        Create New Event
      </a>
    {{-- <a href="javascript:void(0)" class="btn btn-success" style="margin: 0px 0 24px 0">Create New Event</a> <br> --}}
    <table class="table table-bordered event-datatable" id="event-datatable">
        <thead>
            <tr>
                <th>No</th>
                <th>Name</th>
                <th>Slug</th>
                <th>Start At</th>
                <th>End At</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
</div>
</body>

<script type="text/javascript">

    $(document).ready(function() {

    $('.datepicker-start').datepicker({
        dateFormat: 'dd/mm/yy',
    });

    $('.datepicker-end').datepicker({
        dateFormat: 'dd/mm/yy',
    });

    $('body').on('click', '#btn-delete', function (event) {
            var id = $(this).data('id')
            var url = "{{ route('events.destroy', ['id' => ":id"]) }}";
            url = url.replace(':id', id);

            $.ajax({
                method:"DELETE",
                headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                url: url,
                dataType: 'json',
                processData: false,
                contentType: false,
                success: function(res){
                    console.log(res);

                    setTimeout(function(){
                        window.location.reload();
                    }, 500);
                    $("#btn-update").html('Submit');
                    $("#btn-update"). attr("disabled", false);
                },
                error: function(errors) {
                    console.log(errors);
                    setTimeout(function(){
                        window.location.reload();
                    }, 500);
                }
            });
        });

    $('body').on('click','#btn-create', function(){
        var start = $('.datepicker-start').datepicker('getDate')
        var startAt = start.setDate(start.getDate()+1);
        var end = $('.datepicker-end').datepicker('getDate')
        var endAt = end.setDate(end.getDate() +1);
        var start_at = new Date(startAt).toISOString()
        var end_at = new Date(endAt).toISOString()
        var name = $('#name').val();
        var token = "{{ csrf_token() }}";
        var fd = new FormData();
        var url = "{{ route('events.store') }}";

        fd.append('_token', token)
        fd.append('name', name)
        fd.append('start_at', start_at)
        fd.append('end_at', end_at)
        fd.append('_method', 'POST')

        $.ajax({
            method:"POST",
            url: url,
            data:fd,
            dataType: 'json',
            processData: false,
            contentType: false,
            success: function(res){
                // console.log(res);
                if (res.success) {
                    // window.location.href = "leave-application";
                    // callbackSocket();
                    console.log(res)
                }
                setTimeout(function(){
                    window.location.reload();
                }, 500);
            },
            error: function(errors) {

                console.log(errors);
                if(errors.status == 200){
                    window.location.href = "/";
                }else{
                    alert(errors.responseJSON.message);
                }

            }
        });
    })

        fill_datatable();
            function fill_datatable() {
                var table = $('#event-datatable').DataTable({
                    responsive: true,
                    searching: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        type: "GET",
                        url: "{{ route('events.events-index') }}",
                    },
                    columns: [
                        {
                            data: 'custom_id',
                            name: 'custom_id'
                        },
                        {
                            data: 'name',
                            name: 'name'
                        },
                        {
                            data: 'slug',
                            name: 'slug'
                        },
                        {
                            data: 'start_at',
                            name: 'start_at'
                        },
                        {
                            data: 'end_at',
                            name: 'end_at'
                        },
                        {
                            data: 'created_at',
                            name: 'created_at'
                        },
                        {
                            data: 'updated_at',
                            name: 'updated_at'
                        },
                        {
                            data: 'action',
                            name: 'action'
                        },
                    ],
                    columnDefs: [ {
                    orderable: false,
                    className: 'select-checkbox',
                    targets:   0
                } ],
                    lengthMenu: [
                        [10, 50, 100, 1000, -1],
                        [10, 50, 100, 1000, 'All']
                    ]

                });
            }
    });
</script>
</html>
