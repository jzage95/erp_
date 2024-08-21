<?php

namespace App\Http\Controllers;

use App\Services\ClienteUpdateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClienteController extends Controller
{
    protected $clienteUpdateService;

    public function __construct(ClienteUpdateService $clienteUpdateService)
    {
        $this->clienteUpdateService = $clienteUpdateService;
    }

    public function index(Request $request)
    {
        $query = DB::table('Clientes')->orderBy('DataUltimaActualizacao', 'desc');

        if ($request->filled('cliente')) {
            $query->where('Cliente', 'like', '%' . $request->cliente . '%');
        }

        if ($request->filled('numContrib')) {
            $query->where('NumContrib', 'like', '%' . $request->numContrib . '%');
        }

        // Pegue os dados do banco de dados e converta a data para string apenas para exibição
        $clientes = $query->paginate(10)->through(function ($cliente) {
            $cliente->DataUltimaActualizacao = Carbon::parse($cliente->DataUltimaActualizacao)
                ->timezone('Africa/Luanda')
                ->format('d-m-Y H:i:s');
            return $cliente;
        });

        return view('clientes.index', compact('clientes'));
    }

    public function edit($id)
    {
        $cliente = DB::table('Clientes')->where('Cliente', $id)->first();

        if ($cliente) {
            // Convertendo todos os campos do cliente para UTF-8
            array_walk_recursive($cliente, function (&$item) {
                if (is_string($item) && mb_detect_encoding($item, 'UTF-8', true) === false) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'auto');
                }
            });
            return response()->json($cliente);
        }

        return response()->json(['error' => 'Cliente não encontrado'], 404);
    }

    public function update(Request $request, $id)
    {
        // Separar os dados do cliente e o tipo de documento
        $clienteData = $request->only([
            'Cliente',
            'Nome',
            'NumContrib',
            'Fac_Mor',
            'Pais',
            'NomeFiscal'
        ]);

        $tipoDoc = $request->input('tipoDoc');

        // Log para depuração
        Log::info('Iniciando a atualização do cliente', [
            'Cliente' => $id,
            'clienteData' => $clienteData,
            'tipoDoc' => $tipoDoc,
        ]);

        // Verifica se o novo valor do campo "Cliente" já existe no banco de dados, mas diferente do ID atual
        $existe = DB::table('Clientes')
            ->where('Cliente', $clienteData['Cliente'])
            ->where('Cliente', '!=', $id)
            ->exists();

        if ($existe) {
            Log::error('Erro de duplicidade: Novo valor do campo Cliente já existe', ['Cliente' => $clienteData['Cliente']]);
            return redirect()->route('clientes.index')->with('error', 'Erro: Valor duplicado no campo Cliente.');
        }

        // Converte a data para o fuso horário correto
        $clienteData['DataUltimaActualizacao'] = Carbon::now('Africa/Luanda');
        $clienteNome = $clienteData['Nome'];

        // Atualizar os dados do cliente
        $updated = DB::table('Clientes')->where('Cliente', $id)->update($clienteData);

        if ($updated) {
            Log::info('Cliente atualizado com sucesso', ['clienteData' => $clienteData]);
            return redirect()->route('clientes.index')->with('success', 'Cliente <strong>' . $clienteNome . '</strong> atualizado com sucesso.');
        } else {
            Log::error('Falha ao atualizar o cliente', ['Cliente' => $id]);
            return redirect()->route('clientes.index')->with('error', 'Erro ao atualizar o cliente.');
        }
    }

    public function checkCliente(Request $request)
    {
        $clienteId = $request->input('cliente');
        $existe = DB::table('Clientes')->where('Cliente', $clienteId)->exists();

        return response()->json(['exists' => $existe]);
    }


}