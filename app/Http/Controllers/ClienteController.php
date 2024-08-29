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

    public function edit($cliente)
    {
        $cliente = DB::table('Clientes')->where('Cliente', $cliente)->first();

        if ($cliente) {
            // Convertendo todos os campos do cliente para UTF-95
            array_walk_recursive($cliente, function (&$item) {
                if (is_string($item) && mb_detect_encoding($item, 'UTF-8', true) === false) {
                    $item = mb_convert_encoding($item, 'UTF-8', 'auto');
                }
            });
            return response()->json($cliente);
        }

        return response()->json(['error' => 'Cliente não encontrado'], 404);
    }

    public function update(Request $request, $cliente)
    {
        Log::info('Cliente recebido na atualização', ['Cliente' => $cliente]);

        $clienteData = $request->only([
            'Cliente',
            'Nome',
            'NumContrib',
            'Fac_Mor',
            'Pais',
            'NomeFiscal'
        ]);

        $tipoDoc = $request->input('tipoDoc');

        Log::info('Iniciando a atualização do cliente', [
            'Cliente' => $cliente,
            'clienteData' => $clienteData,
            'tipoDoc' => $tipoDoc,
        ]);

        $clienteAlterado = false;
        if ($clienteData['Cliente'] !== $cliente) {
            $clienteAlterado = true;
            // Verifica se o novo valor do campo "Cliente" já existe no banco de dados
            $existe = DB::table('Clientes')
                ->where('Cliente', $clienteData['Cliente'])
                ->exists();

            if ($existe) {
                Log::error('Erro de duplicidade: Novo valor do campo Cliente já existe', ['Cliente' => $clienteData['Cliente']]);
                return redirect()->route('clientes.index')->with('error', 'Erro: Valor duplicado no campo Cliente.');
            }
        }

        $clienteData['DataUltimaActualizacao'] = Carbon::now('Africa/Luanda');
        $clienteNome = $clienteData['Nome'];

        // Log para verificar o cliente que está sendo passado
        Log::info("Chamando updateClienteService com Cliente", ['Cliente' => $cliente, 'clienteAlterado' => $clienteAlterado]);

        // Atualiza o Cliente e a tabela CabecDoc independentemente de o Cliente ter sido alterado
        $this->clienteUpdateService->updateCliente($cliente, $clienteData, $tipoDoc, $clienteAlterado);

        return redirect()->route('clientes.index')->with('success', 'Cliente <strong>' . $clienteNome . '</strong> atualizado com sucesso.');
    }


}