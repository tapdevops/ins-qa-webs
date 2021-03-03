
    <div class="row">
        <div class="col-sm-12">
            @if(session('success'))
            <div class="alert alert-success pull-right" style="width: 300px;">
                <strong>{{session('success')}}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding-top: 0px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
            @if(session('warning'))
            <div class="alert alert-warning pull-right" style="width: 300px;">
                <strong>{{session('warning')}}</strong>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close" style="padding-top: 0px;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            @endif
        </div>
    </div>
    <div class="col-sm-12" style="margin-bottom:10px;">
        <button class="btn btn-primary" onclick="add()" data-toggle="modal" data-target="#modalform">Add</button>
        <input type="text" id="generalSearch" class="form-control" style="width: 300px;float: right;">
    </div>
    <div class="col-sm-12">
        <table class="m-datatable" id="html_table" width="100%">
            <thead>
                <tr>
                    <th style="width:10%">Code</th>
                    <th>Name</th>
                    <th style="width:10%">Icon</th>
                    <th style="width:10%">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($category_finding as $d)
                <tr>
                    <td>{{$d->category_code}}</td>
                    <td>{{$d->category_name}}</td>
                    <td>{{$d->icon}}</td>
                    <td><button class="btn btn-primary" onclick="edit(`{{$d->category_code}}`,`{{$d->category_name}}`,`{{$auth_url. '/files/images/category/' .$d->icon}}`)"  data-toggle="modal" data-target="#modalform" data-id="{{$d->category_code}}">Edit</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="modalform" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
            <form action="{{url('master/category-finding')}}" enctype="multipart/form-data" method="post">
                <input type="hidden" name="_token" id="csrf-token" value="{{ Session::token() }}" />
                <input type="hidden" name="id" value="" />
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_label"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-sm-12">
                            Category Code
                            <input type="text" name="category_code" required class="form-control">
                        </div>
                        <div class="col-sm-12">
                            Category Name
                            <input type="text" name="category_name" required class="form-control">
                        </div>
                        <div class="col-sm-12">
                            Category Icon<br>
                            <div style="display: flex;">
                                <img class="icon" src="" width="43px" height="43px">
                                <input type="file" id="icon" name="icon" onChange="readURL(this);" required class="form-control">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Save changes</button>
                </div>
            </form>
            </div>
        </div>
    </div>
    <script>
        function add(){
            $('#modal_label').html('Add')
            $('input[name=id]').val('');
            $('input[name=category_code]').val('');
            $('input[name=category_code]').removeAttr('readonly');
            $('input[name=category_name]').val('');
            $('input[name=icon]').val('');
            $('input[name=icon]').attr('required','required');
            $('.icon').hide();
        }
        function edit(code,name,icon){
            $('#modal_label').html('Edit')
            $('input[name=id]').val(code);
            $('input[name=category_code]').val(code);
            $('input[name=category_code]').attr('readonly','readonly');
            $('input[name=category_name]').val(name);
            $('input[name=icon]').val('');
            $('input[name=icon]').removeAttr('required');
            $('.icon').show();
            $('.icon').attr('src',icon);
        }

        function readURL(input){
        var ext = input.files[0]['name'].substring(input.files[0]['name'].lastIndexOf('.') + 1).toLowerCase();
        if (input.files && input.files[0] && (ext == "gif" || ext == "png" || ext == "jpeg" || ext == "jpg")) 
            var reader = new FileReader();
            reader.onload = function (e) {
                $('.icon').attr('src', e.target.result);
            }
            reader.readAsDataURL(input.files[0]);
            $('.icon').show();
        }
    </script>