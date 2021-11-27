@extends("master")

@section("content")

<div id="layoutSidenav_content">
    <main>
        <div class="container-fluid">
            @if (session('msg'))
                <div class="alert alert-success mt-4">
                    {{ session('msg') }}
                </div>
            @endif
            
            <form method="POST" action="{{ url('swapping2') }}">
            @csrf
            <div class="card mt-3">
                <div class="card-header bg-primary text-white">
                budget Swapping
            </div>

    <div class="card-body">
        <div class="form-row"> 
                <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="small mb-1" for="year">Year</label>
                                                <select class="custom-select" id="year" name="year_id">
                                                <option value=0>Select Year here</option>
                                                @foreach ($years as $year)
                                                <option value="{{ $year->id }}">{{ $year->year }}</option>
                                                @endforeach
                                                </select>
                                                <span class="small text-danger">{{ $errors->first('year_id') }}</span>
                                            </div>
                </div>
               
                <div class="col-md-6">
                <div class="form-group">
                        <label class="small mb-1" for="dept_id">Department/Branch</label>
                        <select class="custom-select" id="dept_id" name="to_dept">
                            <option value=0>Select Dept/Branch here</option>
                        </select>
                        <span class="small text-danger">{{ $errors->first('to_dept') }}</span>
                        <input type='hidden' id='dept' name='department' value=''>
                    </div>
                </div>

        </div>  
        <div class="form-row">     
                <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="small mb-1" for="category">Category</label>
                                                <select class="custom-select category" id="category" name="category_id">
                                                    <option value=0>Select Category here</option>
                                                    @foreach ($categories as $category)
                                                    <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                                                    @endforeach
                                                </select>
                                                <span class="small text-danger">{{ $errors->first('category_id') }}</span>
                                            </div>
                    </div>
                
                <div class="col-md-6">
                <div class="form-group">
                                                            <label class="small mb-1" for="qty">Quantity</label>
                                                            <input class="form-control py-2" id="qty" name="qty" type="number" placeholder="Enter quantity here" />
                                                            <span class="small text-danger">{{ $errors->first('qty') }}</span>
                                                        </div>
                </div>
        </div>
        <div class="form-row">
                <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="small mb-1" for="subcategory">Sub Category</label>
                                                <select class="custom-select subcategory" id="subcategory" name="sub_cat_id">
                                                <option value=0>Select Sub Category here</option>
                                                </select>
                                                <span class="small text-danger">{{ $errors->first('sub_cat_id') }}</span>
                                            </div>
                </div> 
        </div>

                
                <div class="form-row">
                    <div class="col-md-6">
                    <div class="form-group">
                        <label class="small mb-1" for="from_dept">Department/Branch</label>
                        <select class="custom-select" id="from_dept" name="from_dept">
                            <option value=0>Select Dept/Branch here</option>
                        </select>
                        <span class="small text-danger">{{ $errors->first('from_dept') }}</span>
                        <input type='hidden' id='dept' name='department' value=''>
                    </div>
                </div>
</div>


                <div class="row mt-4"> 
                    <div class="col-md-12 col-lg-12"> 
                        <button type="submit" name="swap" class="btn btn-success float-right">Swap this budget</button>
                    </div>
                </div>
            </div>  
            </div>
            </form>   
        </div>
    </main>
    <footer class="py-4 bg-light mt-auto">
        <div class="container-fluid">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">Copyright &copy; Your Website 2020</div>
                <div>
                    <a href="#">Privacy Policy</a>
                    &middot;
                    <a href="#">Terms &amp; Conditions</a>
                </div>
            </div>
        </div>
    </footer>
</div>
           
@endsection