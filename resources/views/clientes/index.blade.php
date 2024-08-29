@extends('layouts/main')

@section('title', 'Listagem de Clientes')

@section('content')

    <div class="container mt-4">
        <h1 class="text-center mb-4">Listagem de Clientes</h1>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {!! session('success') !!}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form method="GET" action="{{ route('clientes.index') }}">
            <div class="form-row mb-4">
                <div class="col-md-4">
                    <label for="cliente">Cliente:</label>
                    <input type="text" class="form-control" id="cliente" name="cliente"
                        value="{{ request('cliente') }}">
                </div>
                <div class="col-md-4">
                    <label for="numContrib">Número de Contribuinte:</label>
                    <input type="text" class="form-control" id="numContrib" name="numContrib"
                        value="{{ request('numContrib') }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-lg mt-3">Filtrar</button>
                </div>
            </div>
        </form>

        <div class="container mt-6">
            <table class="table table-striped table-hover" style="width:110%;">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col">Cliente</th>
                        <th scope="col">Nome</th>
                        <th scope="col">Morada</th>
                        <th scope="col">Num Contribuinte</th>
                        <th scope="col">País</th>
                        <th scope="col">Data Última Atualização</th>
                        <th scope="col">Ações</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($clientes as $cliente)
                        <tr>
                            <td>{{ $cliente->Cliente }}</td>
                            <td>{{ $cliente->Nome }}</td>
                            <td>{{ $cliente->Fac_Mor }}</td>
                            <td>{{ $cliente->NumContrib }}</td>
                            <td>{{ $cliente->Pais }}</td>
                            <td>{{ $cliente->DataUltimaActualizacao }}</td>
                            <td>
                                <a href="#" class="edit-btn" data-id="{{ $cliente->Cliente }}">
                                    <i class="icon-edit fa-solid fa-pen-to-square" title="Editar Cliente"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- Centralize a paginação -->
        <div class="container mt-4">
            <div class="row">
                <div class="col-md-12 d-flex justify-content-center">
                    {{ $clientes->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="editClientModal" tabindex="-1" aria-labelledby="editClientModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editClientModalLabel">Editar Cliente</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- O formulário de edição será carregado aqui via AJAX -->
                    <form id="editClientForm" method="POST" action="{{ route('clientes.update', $cliente->Cliente) }}">
                        @csrf
                        @method('PUT')

                        <!-- Campos do formulário de edição -->
                        <div class="form-group">
                            <label for="Cliente">Cliente</label>
                            <input type="text" class="form-control" id="Cliente" name="Cliente" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="Nome">Nome</label>
                            <input type="text" class="form-control" id="Nome" name="Nome" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="Fac_Mor">Morada</label>
                            <input type="text" class="form-control" id="Fac_Mor" name="Fac_Mor" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="NumContrib">Número de Contribuinte</label>
                            <input type="text" class="form-control" id="NumContrib" name="NumContrib" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="Pais">País</label>
                            <input type="text" class="form-control" id="Pais" name="Pais" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="NomeFiscal">Nome Fiscal</label>
                            <input type="text" class="form-control" id="NomeFiscal" name="NomeFiscal" value="" required>
                        </div>
                        <div class="form-group">
                            <label for="tipoDoc">Tipo de Documento</label>
                            <select class="form-control" id="tipoDoc" name="tipoDoc" required>
                                <option value="clinica">Faturas Clínica</option>
                                <option value="comercial">Faturas Comercial</option>
                                <option value="renda">Faturas Renda de Casa</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mt-3">Actualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection
