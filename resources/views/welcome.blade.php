<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body>
    @php
        use App\Models\pay;
        use App\Models\User;
        $department = User::distinct()->where('department','!=','0')->get(['department']);
        $pay = DB::table('users')->join('pays', 'users.id', '=', 'pays.id_user')
                    ->select('users.department','users.name','pays.*')
                    ->get();
        $payAll = pay::all();
        $sum = $payAll->sum('spending');
        $count = $payAll->count();
    @endphp
    <div class="p-5">
        <div>
            <p class="d-inline-flex gap-1" style="cursor: pointer">
                <i class='bx bx-cog bx-spin bx-sm'
                    @can('admin')
                        data-bs-toggle="collapse" data-bs-target="#collapseExample"
                        aria-expanded="false" aria-controls="collapseExample"
                    @elsecannot('admin')
                        data-bs-toggle="modal" data-bs-target="#modalLogin" 
                    @endcan
                >
                </i>
                @can('admin')
                    <a href="{{ route('logout') }}" class="btn btn-outline-danger btn-sm ms-4">Đăng xuất</a>
                    <a href="{{ route('export') }}" class="btn btn-primary btn-sm ms-1">Export Excel</a>
                @endcan
            </p>
            @can('admin')
                <div class="collapse show" id="collapseExample">
                    <div class="card card-body" >
                        <div class="row">
                            <div class="col-md-7">
                                <form id="form-department" action="{{ route('insertPay') }}" method="POST">
                                    @csrf
                                    <h5>Chọn ban tham gia 
                                        <button type="button" id="submit-department"
                                        class="btn btn-success btn-sm">Xác nhận</button>
                                    </h5>
                                    <hr>
                                    @foreach ($department as $itm_department)
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox"
                                                value="{{ $itm_department->department }}" id="flexCheck{{$itm_department->department}}"
                                                name="department[]">
                                            <label class="form-check-label" for="flexCheck{{$itm_department->department}}">
                                                {{ $itm_department->department }}
                                            </label>
                                        </div>
                                    @endforeach
                                </form>
                            </div>
                            <div class="col-md-4">
                                <form action="{{ route('insertUser') }}" method="POST" class="row g-3" autocomplete="off">
                                    @csrf
                                    <h5 class="text-center">Thêm thành viên</h5>
                                    <div class="col-md-12">
                                        <input type="text" class="form-control w-100" name="name_user" id="names"
                                            placeholder="Họ tên">
                                    </div>
                                    <div class="col-md-12">
                                        <input type="text" class="form-control w-100" name="department_user"
                                            id="department_user" placeholder="Ban" list="department_ulst">
                                        <datalist id="department_ulst">
                                            @foreach ($department as $itm_dp_addUser)
                                                <option value="{{ $itm_dp_addUser->department }}">
                                            @endforeach
                                        </datalist>
                                    </div>
                                    <div class="col-md-12">
                                        <button class="btn btn-secondary w-100" type="submit">Lưu</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @elsecannot('admin')
                <!-- Modal -->
                <div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="exampleModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-sm">
                        <div class="modal-content">
                            <div class="modal-body">
                                <div class="text-center mb-3">
                                    <a href="{{ route('insertAdmin') }}" class="text-danger" style="cursor: default"><i class='bx bxs-heart bx-md'></i></a>
                                </div>
                                <form action="{{ route('login') }}" method="POST" id="form-login">
                                    @csrf
                                    <input type="text" class="form-control mb-3" name="email" placeholder="Email">
                                    <input type="password" class="form-control mb-3" name="password" placeholder="Password">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember_token" id="remember">
                                        <label class="form-check-label" for="remember">
                                            Ghi nhớ tài khoản
                                        </label>
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Đóng</button>
                                <button type="button" class="btn btn-primary btn-sm" id="submit-login">Đăng nhập</button>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        </div>
        <div class="mt-3 row d-flex justify-content-center">
            <div class="@can('admin') col-md @elsecannot('admin') col-md-8 @endcan table-responsive p-1">
                <table class="table table-hover">
                    <thead class="table-secondary">
                        <tr>
                            <th scope="col">STT</th>
                            <th scope="col">Họ tên</th>
                            <th scope="col">Ban</th>
                            <th scope="col">Số tiền</th>
                            <th scope="col" class="text-center">Ảnh CK</th>
                            <th scope="col" class="text-center">Thanh toán</th>
                            @can('admin')
                                <th scope="col" class="text-center">Tiền chi + ảnh QR</th>
                                <th scope="col" class="text-center">
                                    @if ($pay == '[]')
                                        ...
                                    @else
                                        <a href="{{ route('truncatePay') }}" class="text-decoration-none"
                                            data-bs-toggle="tooltip" data-bs-placement="top"
                                            data-bs-title="Truncate Pay"
                                        >
                                            <i class='bx bx-refresh bx-spin bx-sm text-light bg-warning rounded-pill'></i>
                                        </a>
                                    @endif
                                </th>
                            @endcan
                        </tr>
                    </thead>
                    <tbody>
                        @if ($count > 0)
                            @foreach ($pay as $key => $itm_tb_pay)
                                <tr
                                    @if (($key+1)%2 == 0 )
                                        class="table-light"
                                    @endif
                                >
                                    <th scope="row">{{ $key+1 }}</th>
                                    <td>{{ $itm_tb_pay->name }}</td>
                                    <td>{{ $itm_tb_pay->department }}</td>
                                    <td>{{ number_format($itm_tb_pay->price) }}</td>
                                    <td class="text-center">
                                        @if (!empty($itm_tb_pay->img))
                                            <!-- Button trigger modal -->
                                            <i class='bx bx-qr-scan bx-tada bx-sm text-primary'
                                                data-bs-toggle="modal" 
                                                data-bs-target="#modalImg{{$itm_tb_pay->id}}"
                                                style="cursor: pointer;"
                                            ></i>
                                            <!-- Modal -->
                                            <div class="modal fade" id="modalImg{{$itm_tb_pay->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-body">
                                                            <img src="{{ Storage::url($itm_tb_pay->img) }}" alt="" style="
                                                                max-width: 456px;
                                                                max-height: 620px;
                                                                object-fit: scale-down;
                                                            ">
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>                              
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($itm_tb_pay->status == '1')
                                            <i class='bx bx-check-double bx-sm text-success'></i>
                                        @else
                                            {{ $itm_tb_pay->status }}
                                        @endif
                                    </td>
                                    @can('admin')
                                        <td>
                                            <form action="{{ route('insertSpending', ['id'=>$itm_tb_pay->id]) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="input-group">
                                                    <input type="tel" class="form-control" name="spending"
                                                        value="@if(!empty($itm_tb_pay->spending)){{$itm_tb_pay->spending}}@endif"
                                                        placeholder="Nhập số tiền chi"
                                                    >
                                                    <input type="file" name="img_qrcode" class="form-control" id="inputGroupFile04" aria-describedby="inputGroupFileAddon04" aria-label="Upload">
                                                    <button class="btn btn-secondary" type="submit" id="inputGroupFileAddon04">
                                                        <i class='bx bx-plus-medical text-light'></i>
                                                    </button>
                                                </div>
                                            </form>  
                                        </td>
                                        <td>
                                            <a href="{{ route('checkPay', ['id'=>$itm_tb_pay->id]) }}" class="text-success text-decoration-none"
                                                data-bs-toggle="tooltip" data-bs-placement="left"
                                                data-bs-title="Check or Uncheck"    
                                            >
                                                <i class='bx bxs-check-circle bx-sm'></i>
                                            </a>
                                            <a href="{{ route('destroyMemberPay', ['id'=>$itm_tb_pay->id]) }}" class="text-danger text-decoration-none"
                                                data-bs-toggle="tooltip" data-bs-placement="top"
                                                data-bs-title="Delete member"     
                                            >
                                                <i class='bx bxs-x-circle bx-sm' ></i>
                                            </a>
                                        </td>
                                    @endcan
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="table-dark">
                        <tr>
                            <td colspan="@can('admin') 8 @elsecannot('admin') 6 @endcan">Tổng tiền: {{ number_format($sum) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous">
    </script>
    <script>
        const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]')
        const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl))
    </script>
    <script>
        var formdepartment = document.getElementById("form-department");
        document.getElementById("submit-department").addEventListener("click", function () {
            formdepartment.submit();
        });
    </script>
    <script>
        var formlogin = document.getElementById("form-login");
        document.getElementById("submit-login").addEventListener("click", function () {
            formlogin.submit();
        });
    </script>
</body>

</html>