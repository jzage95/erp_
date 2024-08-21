@extends('layouts/main')

@section('title', 'Editar Cliente')

@section('content')

    <div class="container mt-4">
        <h1 class="text-center mb-4">Editar Cliente</h1>
        <form action="{{ route('clientes.update', $cliente->Cliente) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="Cliente">Cliente:</label>
                <input type="text" class="form-control" id="Cliente" name="Cliente" value="{{ $cliente->Cliente }}">
            </div>
            <div class="form-group">
                <label for="Nome">Nome:</label>
                <input type="text" class="form-control" id="Nome" name="Nome" value="{{ $cliente->Nome }}">
            </div>
            <div class="form-group">
                <label for="Fac_Mor">Morada:</label>
                <input type="text" class="form-control" id="Fac_Mor" name="Fac_Mor" value="{{ $cliente->Fac_Mor }}">
            </div>
            <div class="form-group">
                <label for="NumContrib">Número de Contribuinte:</label>
                <input type="text" class="form-control" id="NumContrib" name="NumContrib"
                    value="{{ $cliente->NumContrib }}">
            </div>
            <div class="form-group">
                <label for="Pais">País:</label>
                <input type="text" class="form-control" id="Pais" name="Pais" value="{{ $cliente->Pais }}">
            </div>
            <div class="form-group">
                <label for="NomeFiscal">Nome Fiscal:</label>
                <input type="text" class="form-control" id="NomeFiscal" name="NomeFiscal"
                    value="{{ $cliente->NomeFiscal }}">
            </div>
            <div class="form-group">
                <label for="tipoDoc">Tipo de Documento:</label>
                <select class="form-control" id="tipoDoc" name="tipoDoc">
                    <option value="clinica" {{ request('tipoDoc') == 'clinica' ? 'selected' : '' }}>Faturas Clínica</option>
                    <option value="comercial" {{ request('tipoDoc') == 'comercial' ? 'selected' : '' }}>Faturas Comercial
                    </option>
                    <option value="renda" {{ request('tipoDoc') == 'renda' ? 'selected' : '' }}>Faturas Renda de Casa
                    </option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary btn-lg mt-3">Atualizar</button>
        </form>
    </div>

@endsection
