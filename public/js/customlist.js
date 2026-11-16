$("document").ready(function(){
                           
        //  $("#brand").change(function(){ 
        //      var bid = $(this).val();
        //       var _token   = $('meta[name="csrf-token"]').attr('content');
        //       $.ajax({
        //         type: 'POST',
        //         url: '../getmidcidbybid',
        //         data:{
        //               bid:bid,
        //               _token: _token
        //             },
        //         dataType: "json",
        //         success:function(response){
        //             console.log(response);
        //             $("#shadecardbycategoryselected").html(response);
        //                  }
        //               });
        //          });
         $("#maincategoryforajax").change(function(){
             var mid = $(this).val();
              $("#subcatforajax").html('');
              var _token   = $('meta[name="csrf-token"]').attr('content');
              $.ajax({
                type: 'POST',
                url: 'getsubcat',
                data:{
                      mid:mid,
                      _token: _token
                    },
                dataType: "json",
                success:function(response){
                    //console.log(response);
                    $("#subcatforajax").html(response);
                         }
                      });
                 });
                 
         $("#maincategoryforajaxforproduct").change(function(){
             var mid = $(this).val();
              $("#subcatforajax").html('');
              var _token   = $('meta[name="csrf-token"]').attr('content');
              $.ajax({
                type: 'POST',
                url: '../getsubcat',
                data:{
                      mid:mid,
                      _token: _token
                    },
                dataType: "json",
                success:function(response){
                    //console.log(response);
                    $("#subcatforajax").html(response);
                         }
                      });
                 });
                 
                 ////////////////////////add product page me shadecard by category
                       
         $("#subcatforajax").change(function(){ 
             var mid = $(this).val();
              $("#shadecardbycategoryselected").html('');
              var _token   = $('meta[name="csrf-token"]').attr('content');
              $.ajax({
                type: 'POST',
                url: '../getshadecardlist',
                data:{
                      catid:mid,
                      _token: _token
                    },
                dataType: "json",
                success:function(response){
                    console.log(response);
                    $("#shadecardbycategoryselected").html(response);
                         }
                      });
                 });
                 
 });                 
function getComboA(selectObject) {
    var _token   = $('meta[name="csrf-token"]').attr('content');
                      var value = selectObject.value;  
                      if(value=='1'){
                              $.ajax({
                                type: 'POST',
                                url: 'getbrandlist',
                                data:{
                                      _token: _token
                                    },
                                dataType: "json",
                                success:function(response){
                                    $("#brandpro").html(response);
                                         }
                                      });
                      }else{
                              $.ajax({
                                type: 'POST',
                                url: 'getproductlist',
                                data:{
                                      _token: _token
                                    },
                                dataType: "json",
                                success:function(response){
                                    $("#brandpro").html(response);
                                         }
                                      });
                      }
                    }

                             
                    
function checkallitsrow(id){ 
                                    var checkboxItem = ".checkofrow"+id;
                                    var headcheck = $('input.checkAll'+id+':checked').val();
                                      if (headcheck=='on') {
                                        $(checkboxItem).each(function() { 
                                          this.checked = true;
                                        });
                                      } else {
                                        $(checkboxItem).each(function() {
                                          this.checked = false;
                                        });
                                      }
                              }                    
                                       
        function updateattributeprice(id){
              var factoryPrice = $("#factory_price"+id).length ? $("#factory_price"+id).val() : $("#price"+id).val();
              var displayPrice = $("#display_price"+id).length ? $("#display_price"+id).val() : $("#price"+id).val();
              var isGstIncluded = $("#gst_included"+id).is(':checked') ? 1 : 0;
              var gstRate = $("#gst_rate"+id).length ? $("#gst_rate"+id).val() : null;
              var mrp = $("#mrp"+id).length ? $("#mrp"+id).val() : '';
              var _token   = $('meta[name="csrf-token"]').attr('content');

              $.ajax({
                type: 'POST',
                url: '/seller/updateattributeprice',
                data:{
                      aid: id,
                      seller_price: factoryPrice,
                      price: displayPrice,
                      is_gst_included: isGstIncluded,
                      gst_rate: gstRate,
                      mrp: mrp,
                      _token: _token
                    },
                dataType: "json",
                success:function(response){
                    if(response.status==true){
                        var msg = "Pricing successfully updated!";
                        if(isGstIncluded == 1){
                            msg += "\n\n(Product routed to Admin Review Bucket for display price verification)";
                        }
                        alert(msg);
                    }else{
                        alert("Update failed: " + (response.message || "Something went wrong"));
                    }
                },
                error: function(xhr){
                    $.ajax({
                        type: 'POST',
                        url: '../../updateattributeprice',
                        data:{
                              aid: id,
                              seller_price: factoryPrice,
                              price: displayPrice,
                              is_gst_included: isGstIncluded,
                              mrp: mrp,
                              _token: _token
                            },
                        dataType: "json",
                        success:function(response){
                            if(response.status==true){alert("Pricing successfully updated!");}
                            else{alert("Update failed");}
                        },
                        error: function(){
                            alert("Network error: Could not update pricing.");
                        }
                    });
                }
              });
        }           
                    
                                       
        function getmaincatbybrand(id){ 
              var cid = id;
              var mid = $("#maincategoryforajaxforproduct").val();
              var _token   = $('meta[name="csrf-token"]').attr('content');
              $.ajax({
                type: 'POST',
                url: '../getmaincatandcatbybrand',
                data:{
                      cid:id,mid:mid,
                      _token: _token
                    },
                dataType: "json",
                success:function(response){
                    //  $("#maincategoryforajaxforproduct").html(response.mid);
                     $("#subcatforajax").html(response.cid);
                      $("#shadecardbycategoryselected").html(response.shadecard);
                         }
                      });
        }                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    
                    