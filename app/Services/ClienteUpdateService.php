<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ClienteUpdateService
{
    public function updateCliente($oldClienteId, $data, $tipoDoc, $clienteAlterado)
    {
        DB::transaction(function () use ($oldClienteId, $data, $tipoDoc, $clienteAlterado) {
            // Log para depuração
            Log::info('updateCliente chamado', [
                'oldClienteId' => $oldClienteId,
                'data' => $data,
                'tipoDoc' => $tipoDoc,
                'clienteAlterado' => $clienteAlterado,
            ]);

            // Verifica se o ID do Cliente está correto
            $cliente = DB::table('Clientes')->where('Cliente', $oldClienteId)->first();
            if (!$cliente) {
                throw new \Exception('Cliente não encontrado para o ID fornecido.');
            }

            // Atualiza os dados do Cliente na tabela 'Clientes'
            $updated = DB::table('Clientes')->where('Cliente', $oldClienteId)->update($data);

            if (!$updated) {
                throw new \Exception('Erro ao atualizar o cliente na tabela Clientes.');
            }

            // Atualiza a tabela CabecDoc com base nos dados atualizados
            $this->updateCabecDoc($oldClienteId, $data, $tipoDoc, $clienteAlterado);

            Log::info('Cliente e CabecDoc atualizados com sucesso.', ['clienteData' => $data]);
        });
    }

    private function updateCabecDoc($oldClienteId, $data, $tipoDoc, $clienteAlterado)
    {
        // Verifique os tipos de documento que devem ser atualizados na tabela CabecDoc
        $tiposDoc = $this->getTiposDocByTipoDoc($tipoDoc);

        // Atualiza a tabela CabecDoc para cada tipo de documento associado ao Cliente
        foreach ($tiposDoc as $doc) {
            $query = DB::table('CabecDoc')
                ->where('Entidade', $oldClienteId)
                ->where('TipoDoc', $doc);

            if ($clienteAlterado) {
                // Se o Cliente foi alterado, atualize também o campo Entidade
                $updated = $query->update([
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
            } else {
                // Se o Cliente não foi alterado, atualize apenas os outros campos
                $updated = $query->update([
                    'NumContribuinte' => $data['NumContrib'],
                    'Nome' => $data['Nome'],
                    'Morada' => $data['Fac_Mor'],
                    'PaisFac' => $data['Pais'],
                    'NomeFac' => $data['NomeFiscal'],
                    'NumContribuinteFac' => $data['NumContrib'],
                ]);
            }

            if (!$updated) {
                Log::warning("Nenhum registro foi atualizado na tabela CabecDoc para o TipoDoc {$doc}.");
            } else {
                Log::info("Registro atualizado com sucesso na tabela CabecDoc para o TipoDoc {$doc}.");
            }
        }
    }

    private function getTiposDocByTipoDoc($tipoDoc)
    {
        switch ($tipoDoc) {
            case 'clinica':
                return ['VDC', 'FAC'];
            case 'comercial':
                return [
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
            case 'renda':
                return ['FAR'];
            default:
                return []; // Caso padrão para evitar problemas
        }
    }
}

