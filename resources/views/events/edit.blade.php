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
        <div class="card">
            <div class="card-header">
                <h5 class="modal-title" id="create_title">Show Event #{{$events->id}}</h5>
            </div>
            <div class="card-body">
                <div class="form-group">
                    <label class="required" for="show_name">Name</label>
                    <input type="text" class="form-control show_name" value="{{$events->name}}" id="name" >
                </div>

                {{-- <div class="form-group">
                    <label class="required" for="show_slug">Slug</label>
                    <input type="text" class="form-control show_slug" value="{{$events->slug}}" id="show_slug" >
                </div> --}}
                <div class="input-group date " data-provide="datepicker" style="gap: 20px">
                    <div class="flex">
                        <label class="required" for="show_start">Start At</label>
                        <input type="text" class="form-control datepicker-start" value={{$start_at}}  id="start" >
                    </div>
                    <div class="flex">
                        <label class="required" for="end">End At</label>
                        <input type="text" class="form-control datepicker-end" value={{$end_at}}  id="end" >
                    </div>
                </div><br>
            </div>
            <div class="modal-footer">
                <a type="button" class="btn btn-secondary"  href="{{ url('/events') }}">Close</a>
                <button type="button" class="btn btn-primary" id="btn-edit">Save</button>
            </div>
        </div>
    </div>
</body>

<script>
$(document).ready(function() {
    $('.datepicker-start').datepicker({
        dateFormat: 'dd/mm/yy',
    });

    $('.datepicker-end').datepicker({
        dateFormat: 'dd/mm/yy',
    });

    $('body').on('click','#btn-edit', function(){
        var start = $('.datepicker-start').datepicker('getDate')
        var startAt = start.setDate(start.getDate()+1);
        var end = $('.datepicker-end').datepicker('getDate')
        var endAt = end.setDate(end.getDate() +1);
        var start_at = new Date(startAt).toISOString()
        var end_at = new Date(endAt).toISOString()
        var name = $('#name').val();
        var token = "{{ csrf_token() }}";
        var fd = new FormData();
        var id = <?php echo json_encode($events->id);?>;
        var url = "{{ route('events.update', ":id") }}";
        url = url.replace(':id', id);
        fd.append('_token', token)
        fd.append('name', name)
        fd.append('start_at', start_at)
        fd.append('end_at', end_at)
        fd.append('_method', 'PUT')
console.log('assdihabsi')
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
                    window.location.href = "/events";
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

});
</script>
</html>
