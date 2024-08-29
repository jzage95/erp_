<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClienteUpdateService
{
    public function updateCliente($oldCliente, $data, $tipoDoc, $clienteAlterado)
    {
        DB::transaction(function () use ($oldCliente, $data, $tipoDoc, $clienteAlterado) {
            // Log para depuração
            Log::info('updateCliente chamado', [
                'oldCliente' => $oldCliente,
                'data' => $data,
                'tipoDoc' => $tipoDoc,
                'clienteAlterado' => $clienteAlterado,
            ]);

            // Atualiza os dados do Cliente na tabela 'Clientes'
            $updated = DB::table('Clientes')->where('Cliente', $oldCliente)->update($data);

            if (!$updated) {
                throw new \Exception('Erro ao atualizar o cliente na tabela Clientes.');
            }

            // Atualiza a tabela CabecDoc com base nos dados atualizados
            $this->updateCabecDoc($oldCliente, $data, $tipoDoc, $clienteAlterado);

            Log::info('Cliente e CabecDoc atualizados com sucesso.', ['clienteData' => $data]);
        });
    }

    private function updateCabecDoc($oldCliente, $data, $tipoDoc, $clienteAlterado)
    {
        // Verifique os tipos de documento que devem ser atualizados na tabela CabecDoc
        $tiposDoc = [];
        switch ($tipoDoc) {
            case 'clinica':
                $tiposDoc = ['VDC', 'FAC'];
                break;
            case 'comercial':
                $tiposDoc = [
                    'FT',
                    'FT03',
                    'FT04',
                    'NC03',
                    'NC04',
                    'EP13',
                    'EP14',
                    'EP15',
                    'EP17',
                    'NC',
                    'NC13',
                    'NC14',
                    'NC15',
                    'NC17'
                ];
                break;
            case 'renda':
                $tiposDoc = ['FAR'];
                break;
            default:
                $tiposDoc = []; // Caso padrão para evitar problemas
                break;
        }

        // Atualiza a tabela CabecDoc para cada tipo de documento associado ao Cliente
        foreach ($tiposDoc as $doc) {
            $updated = DB::table('CabecDoc')
                ->where('Entidade', $oldCliente)
                ->where('TipoDoc', $doc)
                ->update([
                    'Entidade' => $data['Cliente'],
                    'NumContribuinte' => $data['NumContrib'],
                    'Nome' => $data['Nome'],
                    'Morada' => $data['Fac_Mor'],
                    'PaisFac' => $data['Pais'],
                    'NomeFac' => $data['NomeFiscal'],
                    'NumContribuinteFac' => $data['NumContrib'],
                    'EntidadeFac' => $data['Cliente'],
                    'EntidadeEntrega' => $data['Cliente'],
                    'EntidadeDescarga' => $data['Cliente'],
                ]);

            if (!$updated) {
                Log::warning("Nenhum registro foi atualizado na tabela CabecDoc para o TipoDoc {$doc}.");
            }
        }
    }
}
