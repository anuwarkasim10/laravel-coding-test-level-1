@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
    <div class="card-header">
       <h2>Country List</h2>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class=" table table-bordered table-striped table-hover datatable datatable-Leave">
                <thead>
                    <tr>
                        <th>
                            Country Name
                        </th>
                        <th>
                            Capital
                        </th>
                        <th>
                            Region
                        </th>
                        <th>
                            Flag
                        </th>
                        <th>
                            Population
                        </th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($result as $key => $country)

                      <tr data-entry-id="">
                          <td>
                            {{$country->name->common ?? ''}}
                          </td>
                          <td>
                            {{$country->capital[0] ?? ''}}
                          </td>
                          <td>
                            {{$country->region ?? ''}}
                          </td>
                          <td>
                            {{$country->flag ?? ''}}
                          </td>
                          <td>
                            {{$country->population ?? ''}}
                          </td>
                      </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
