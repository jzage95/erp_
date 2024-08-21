<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClienteUpdateService
{
    public function updateCliente($oldClienteId, $data, $tipoDoc)
    {
        DB::transaction(function () use ($oldClienteId, $data, $tipoDoc) {
            // Log para depuração
            Log::info('updateCliente chamado', [
                'oldClienteId' => $oldClienteId,
                'data' => $data,
                'tipoDoc' => $tipoDoc,
            ]);
    
            // Determinar os tipos de documentos com base na seleção
            $tiposDoc = [];
            switch ($tipoDoc) {
                case 'clinica':
                    $tiposDoc = ['VDC', 'FAC'];
                    break;
                case 'comercial':
                    $tiposDoc = [
                        'FT', 'FT03', 'FT04', 'NC03', 'NC04', 'EP13', 'EP14', 
                        'EP15', 'EP17', 'NC', 'NC13', 'NC14', 'NC15', 'NC17'
                    ];
                    break;
                case 'renda':
                    $tiposDoc = ['FAR'];
                    break;
                default:
                    $tiposDoc = []; // Caso padrão para evitar problemas se tipoDoc não corresponder a nenhum caso
                    break;
            }
    
            // Log dos tipos de documentos selecionados
            Log::info('tiposDoc selecionado', ['tiposDoc' => $tiposDoc]);
    
            // Atualiza a tabela CabecDoc com a nova entidade sempre, mesmo que o Cliente não tenha mudado
            $this->updateCabecDocWithStoredProcedure($oldClienteId, $data, $tiposDoc);
    
            // Atualiza os dados do Cliente na tabela 'Clientes'
            $updated = DB::table('Clientes')->where('Cliente', $oldClienteId)->update($data);
    
            // Log do sucesso ou falha da atualização
            if ($updated) {
                Log::info('Cliente atualizado com sucesso', ['clienteData' => $data]);
            } else {
                Log::error('Falha ao atualizar o Cliente', ['oldClienteId' => $oldClienteId, 'clienteData' => $data]);
            }
        });
    }

    private function updateCabecDocWithStoredProcedure($oldClienteId, $data, $tiposDoc)
    {
        if (empty($tiposDoc)) {
            Log::info('Nenhum tipoDoc selecionado, abortando updateCabecDocWithStoredProcedure.');
            return; // Evitar execução se tiposDoc estiver vazio
        }

        // Convertendo o array de tipos de documentos em uma string para passar para a Stored Procedure
        $tiposDocString = implode("','", $tiposDoc);
        $tiposDocString = "'".$tiposDocString."'";

        // Log antes de chamar a Stored Procedure
        Log::info('Chamando Stored Procedure para atualizar a tabela CabecDoc', [
            'oldClienteId' => $oldClienteId,
            'newClienteId' => $data['Cliente'],
            'NumContrib' => $data['NumContrib'],
            'Nome' => $data['Nome'],
            'Fac_Mor' => $data['Fac_Mor'],
            'Pais' => $data['Pais'],
            'NomeFiscal' => $data['NomeFiscal'],
            'tiposDoc' => $tiposDocString
        ]);

        try {
            // Chamar a Stored Procedure
            DB::statement('EXEC UpdateCabecDocEntidade @OldEntidade = ?, @NewEntidade = ?, @NumContrib = ?, @Nome = ?, @Fac_Mor = ?, @Pais = ?, @NomeFiscal = ?, @TiposDoc = ?', [
                $oldClienteId,
                $data['Cliente'],
                $data['NumContrib'],
                $data['Nome'],
                $data['Fac_Mor'],
                $data['Pais'],
                $data['NomeFiscal'],
                $tiposDocString
            ]);

            // Log após a chamada da Stored Procedure
            Log::info('Stored Procedure chamada para atualizar a tabela CabecDoc', [
                'oldClienteId' => $oldClienteId,
                'newClienteId' => $data['Cliente'],
                'tiposDoc' => $tiposDocString,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao chamar a Stored Procedure', ['error' => $e->getMessage()]);
        }
    }
}