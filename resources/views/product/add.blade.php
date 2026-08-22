@extends('layout')
@section('title',$title)
@section('content')
<style>
    .dot {
  height: 25px;
  width: 25px;
    border: 1px solid;
    margin-right: 6px;
  border-radius: 50%;
  display: inline-block;
}

   div#shadecardbycategoryselected {
    height: 350px;
    overflow: auto;
}
tr.table-sticky {
    position: sticky;
    top: 0px;
    z-index: 111;
    background: #f9f9f9;
}
table.table.table-striped.table-bordered th {
    position: sticky;
    top: 0px;
    z-index: 111;
}
.table-col-sticky th {
    position: sticky;
    left: 0;}
</style>
<script>
$(".checkAll").click(function(){ alert('45645');
    $('input:checkbox').not(this).prop('checked', this.checked);
});
</script>
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">

				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div class="breadcrumb-title pe-3">Apnifactory</div>
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;">Home</a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">{{$title}}</li>
							</ol>
						</nav>
					</div>
					<div class="ms-auto">
						
					</div>
				</div>
				<!--end breadcrumb-->
               <div class="card">
				  <div class="card-body p-4">
					  <h5 class="card-title">Add New Product</h5>
					  <hr/>
                       <div class="form-body mt-4">
					    <div class="row">
                           
						   <div class="col-lg-12">
						       <form method="post" enctype='multipart/form-data'>
						           @csrf
						           <input type="hidden" name="maincategory" id="maincategoryid" value="">
						           <input type="hidden" name="category" id="categoryid" value="">
                           <div class="border border-3 p-4 rounded">
							  <div class="row">
							<div class="mb-3 col-md-10">
								<label for="inputProductTitle" class="form-label">Product Name</label>
								<input type="text" class="form-control" name="name" placeholder="Enter product title" value="{{ old('name', $step1Data['name'] ?? '') }}" >
							  </div>
							<!--<div class="mb-3">-->
							<!--	<label for="inputProductTitle" class="form-label">Product Slug</label>-->
							<!--	<input type="text" class="form-control" name="slug" placeholder="Enter product title">-->
							<!--  </div>-->
							  
                              <div class="mb-3 col-md-2">
                                <label for="inputProductType" class="form-label">Status</label>
                                <select class="form-select" id="inputProductType" name="status">
                                    <option value="1" {{ old('status', $step1Data['status'] ?? '') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $step1Data['status'] ?? '') == '0' ? 'selected' : '' }}>Deactive</option>
                                  </select>
                              </div>
							  </div>
							  <div class="row">
                              <div class="mb-3 col-md-4">
                                <label for="inputProductType" class="form-label">Product Brand</label>
                                <select class="form-select" id="brand" name="brand" required>
                                    <option value="">Select Brand</option>
                                    @foreach(Helper::getbrand() as $bid)
                                    <option value="{{$bid->id}}" {{ old('brand', $step1Data['brand'] ?? '') == $bid->id ? 'selected' : '' }}>{{$bid->name}}(@php echo $cat = Helper::getcatname($bid->category_id); @endphp)</option>
                                    @endforeach
                                  </select>
                              </div>
                            <?php /*
                              <div class="mb-3 col-md-4">
                                <label for="inputProductType" class="form-label">Main Category</label>
                                <select class="form-select" id="maincategoryforajaxforproduct" name="maincategory" required>
                                    <option value="">Select Main Category</option>
                                     @foreach(Helper::getmaincat() as $mid)
                                    <option value="{{$mid->id}}">{{$mid->name}}</option>
                                    @endforeach
                                  </select>
                              </div>
                              <div class="mb-3 col-md-4">
                                <label for="inputProductType" class="form-label"> Category</label>
                                <!--onchange="getmaincatbybrand(this.value)" -->
                               <select class="form-select" id="subcatforajax" name="category"  required>
                                    <option></option>
                                    <option value="1">OIL PAINT</option>
                                    <option value="2">WATER BASED PRIMER</option>
                                    <option value="3">Taps</option>
                                    <option value="3">Floor Cleaner</option>
                                    <option value="1">DISTEMPER</option>
                                  </select>
                              </div>
                              </div>
                              */  ?>
							  <div class="row">
							<div class="mb-3 col-md-6">
								<label for="inputProductTitle" class="form-label">Product HSN Number</label>
								<input type="text" class="form-control" name="hsncode" value="{{ old('namehsncode', $step1Data['namehsncode'] ?? '') }}"  placeholder="Enter product HSN" required>
							  </div>
							  
                              <div class="mb-3 col-md-6">
                                
  <label class="form-label d-block">Tax Rate</label>
  
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" id="tax5" name="tax" value="5"  {{ old('tax', $step1Data['tax'] ?? '5') == '5' ? 'checked' : '' }}>
    <label class="form-check-label" for="tax5">5%</label>
  </div>
  
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" id="tax12" name="tax" value="12"  {{ old('tax', $step1Data['tax'] ?? '12') == '12' ? 'checked' : '' }}>
    <label class="form-check-label" for="tax12">12%</label>
  </div>
  
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" id="tax28" name="tax" value="28" {{ old('tax', $step1Data['tax'] ?? '28') == '28' ? 'checked' : '' }}>
    <label class="form-check-label" for="tax28">28%</label>
  </div>
  <div class="form-check form-check-inline">
    <input class="form-check-input" type="radio" id="tax18" name="tax" value="18"  {{ old('tax', $step1Data['tax'] ?? '18') == '18' ? 'checked' : '' }} >
    <label class="form-check-label" for="tax18">18%</label>
  </div>
  
</div>
                              </div>
							  <div class="mb-3">
								<label for="inputProductDescription" class="form-label">Product Description</label>
								 <div class="text-editor">
                                            <textarea name="details" class="nic-edit-p" rows="10" style="width:100%">{{ old('details', $step1Data['details'] ?? '') }}</textarea> 
                                        </div>
                                <!--<div class="card">
                                    <div class="card-body">
                                            <textarea id="mytextarea" name="mytextarea">Hello, World!</textarea>
                                    </div>
                                </div> -->
							  </div>
                              <div class="mb-3">
                                <div class="table-responsive"  id="shadecardbycategoryselected">
                                  <?php /*  <table id="" class="table table-striped table-bordered" style="width:100%">
                                        <tbody>
                                        <tr>
                                            <th></th>
                                            @foreach(Helper::getboxpacking() as $bxp)
                                            <th> <input class="form-check-input checkAll{{$bxp->id}}" onClick="checkallitsrow({{$bxp->id}})" type="checkbox" id="checkAll" name="attribu">  {{$bxp->name}} </th>
                                            @endforeach
                                        </tr>
                                       
                                            @foreach(Helper::getcolors() as $gcr)
                                        <tr>
                                            <th><span class="dot" style="background-color: {{$gcr->hexcode}};"></span>{{$gcr->name}}</th>
                                            @foreach(Helper::getboxpacking() as $bxpg)
                                            <td>
                                                <div class="form-check">
                                                    <input class="form-check-input checkofrow{{$bxpg->id}}" type="checkbox" value="{{$bxpg->id}}-&-{{$gcr->id}}" id="flexCheckDefault{{$bxpg->id}}" name="attributes[]">
                                                    <label class="form-check-label" for="flexCheckDefault"></label>
                                                </div>
                                            </td>
                                            @endforeach
                                        </tr>
                                            @endforeach
                                    </tbody>
                                        </table>
                                    */  ?>
                                         
                                        </div>

                              </div>
                              <div class="mb-3">
                                <div class="d-grid">
                                   <button type="submit" class="btn btn-primary">NEXT</button>
                                </div>
                            </div>
                            </div>
                            </form>
						   </div>
						   
					   </div><!--end row-->
					</div>
				  </div>
			  </div>
			</div>
		</div>
		<!--end page wrapper -->
		<script>
 $("document").ready(function(){
                           
         $("#brand").change(function(){ 
             var bid = $(this).val();
              var _token   = $('meta[name="csrf-token"]').attr('content');
              $.ajax({
                type: 'POST',
                url: '../getmidcidbybid',
                data:{
                      bid:bid,
                      _token: _token
                    },
                //dataType: "json",
                success:function(response){
                    console.log(response);
                    //$("#shadecardbycategoryselected").html(response.listdata);
                    let rawHtml = response.listdata; 
                    // Inject into your container element (e.g., #table-container)
                    $('#shadecardbycategoryselected').html(rawHtml);
                    $("#maincategoryid").val(response.mid);
                    $("#categoryid").val(response.cid);
                         }
                      });
                 });
 });
                 </script>
@endsection