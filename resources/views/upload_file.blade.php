
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="viewport" content="width=device-width, initial-scale=1,   shrink-to-fit=no">
        <title style="background-color:grey;">Multiple Image upload</title>

        <script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <style>
        .text-danger{
            color: red;
            padding-left: 113px;
            font-size: 12px;
        }
        .success{
            font-size: 13px;
            color: white;
            background-color: green;
            border: 1px solid;
        }
        .error{
            font-size: 13px;
            color: white;
            background-color: red;
            border: 1px solid;
        }
        .button_for_submit{
            float: right;
            padding: 10px 26px;
            background: darkblue;
            border-radius: 4px;
            color: white;
            outline: none;
            border: none;
            font-weight: 700;
            float: right;
            box-shadow: 0 4px #999;
            cursor: pointer;
        }
        .button_for_submit:active {
            background-color: darkblue;
            box-shadow: 0 1px #666;
            transform: translateY(4px);
        }
        .loader {
            border: 16px solid #f3f3f3; /* Light grey */
            border-top: 16px solid #3498db; /* Blue */
            border-radius: 50%;
            width: 80px;
            height: 80px;
            animation: spin 2s linear infinite;
            display: none;
            position: absolute;
            left: 50%;
            top: 50%;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .opacity-cls{
            opacity: 0.3;
        }

        
    </style>
    </head>
    <body>
        <div class="loader"></div>
        <div class="main_container">
            <div class="container">
                <div style="width:700px; margin:auto;border:1px solid #dadada;padding:10px 50px 80px 30px;">
                    <h3 style="text-align: center;">Upload a Images</h3>
                    <form id="uploadInvoices" enctype="multipart/form-data" >

                        <div class="form-group" style="margin-bottom: 22px;">
                            <label for="username">User Name</label>
                            <input id="username" type="input" required class="form-control" name="usernname" placeholder="Enter Image Name" style="width: 500px;padding: 7px;border-radius: 4px;">
                                <div class="text-danger username" style="display: none;">/div>
                        </div>
                        <div class="form-group" style="margin: 30px 0 22px 0;">
                            <label>Choose Images</label>
                            <input id="images" type="file" required name="images[]" multiple>
                                <div class="text-danger images" style="display: none;"></div>
                        </div>
                        <div class="form-group" style="margin-bottom: 22px;">
                            <button id="submit_button" class="button_for_submit" type="submit">Submit</button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="container">
                <div style="width:780px; margin:auto;border:1px solid #dadada">
                    <div id="append_div">
                        
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>


<script> 

   $('#uploadInvoices').on('submit', function(e) {
       $('.loader').show();
       $('.main_container').addClass('opacity-cls');
       e.preventDefault(); 
       var data = new FormData(this);
        $.each($('#images')[0].files, function(i, file) {
            data.append('images'+i, file);
        });
       var APP_URL = '{{url('/')}}' ;
       var username = $('#username').val();
        data.append('username',username);
       $.ajax({
            headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
           type: "POST",
           url: APP_URL+'/uploadImg',
           dataType: 'json',
           cache:false,
           contentType: false,
           processData: false,
           data: data,
           beforeSend: function() {
                $('#submit_button').attr('disabled',true);
            },
           success: function(response) {
                $('.loader').hide();
                $('.main_container').removeClass('opacity-cls');
               var appendDiv = $('#append_div');
               appendDiv.empty();
               var html ='';
               console.log(response);
               if(response.success.length > 0){        
                    response.success.map(function(value){
                        html += `<div class="success"><span>${value.image_name}<span> : <span>${value.message}</span></div>`
                    });
               }
               
               if(response.error.length > 0){
                    response.error.map(function(value){
                        html += `<div class="error"><span>${value.image_name}<span> : <span>${value.message}</span></div>`
                    });
               }
               appendDiv.append(html);
               $('#submit_button').attr('disabled',false);
               
           },
           error: function(errors){
                $('.loader').hide();
                $('.main_container').removeClass('opacity-cls');
                if(errors.responseJSON.errors.username !== undefined){
                    $('.username').text(errors.responseJSON.errors.username[0]).show();
                }
                if(errors.responseJSON.errors.images !== undefined){
                    $('.images').text(errors.responseJSON.errors.images[0]).show();
                }
                setTimeout(function(){
                    $('.username').text('').hide();
                    $('.images').text('').hide();
                },5000);

                $('#submit_button').attr('disabled',true);
           },
           complete: function (){
                $( '#uploadInvoices' ).each(function(){
                this.reset();
                })
           }
       });
   });


</script>




