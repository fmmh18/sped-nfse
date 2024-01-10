<?php

namespace NFePHP\NFSe\Models\Issnet\Factories;

use NFePHP\NFSe\Models\Issnet\RenderRPS;

class EnviarLoteRps extends Factory
{
    public function render(
        $versao,
        $remetenteTipoDoc,
        $remetenteCNPJCPF,
        $inscricaoMunicipal,
        $lote,
        $rpss
    ) {
        $method = 'EnviarLoteRpsEnvio';
        $xsd = 'servico_enviar_lote_rps_envio';
        $qtdRps = count($rpss);
        $content = '<EnviarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">';
        $content .= '<LoteRps Id="lote01" versao="2.04">';
        $content .= "<NumeroLote>$lote</NumeroLote>";
        $content .= '<Prestador>';
        $content .= "<CpfCnpj>";
        if ($remetenteTipoDoc == '2') {
            $content .= "<Cnpj>$remetenteCNPJCPF</Cnpj>";
        } else {
            $content .= "<Cpf>$remetenteCNPJCPF</Cpf>";
        }
        $content .= "</CpfCnpj>";
        $content .= "<InscricaoMunicipal>$inscricaoMunicipal</InscricaoMunicipal>";
        $content .= "</Prestador>";
        $content .= "<QuantidadeRps>$qtdRps</QuantidadeRps>";
        $content .= "<ListaRps>";
        foreach ($rpss as $rps) {
            //$content .= RenderRPS::toXml($rps, $this->timezone, $this->algorithm, $this->certificate);
            $rps_xml = RenderRPS::toXml($rps, $this->timezone, $this->algorithm, $this->certificate);
            $rps_assinado = Signer::sign($this->certificate, $rps_xml, 'Rps', '', $this->algorithm, [false, false, null, null]);
            $content .= $rps_assinado;
        }
        $content .= "</ListaRps>";
        $content .= "</LoteRps>";
        $content .= "</EnviarLoteRpsEnvio>";

        
        $body = Signer::sign(
            $this->certificate,
            $content,
            'LoteRps',
            '',
            $this->algorithm,
            [false, false, null, null]
        );

        $body = $this->clear($body);

        $msg = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:nfse="http://nfse.abrasf.org.br">';
        $msg .= '<soapenv:Body>';
        $msg .= '<nfse:RecepcionarLoteRps>';
        $msg .= '<nfseCabecMsg>';
        $msg .= '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.04">';
        $msg .= '<versaoDados>2.04</versaoDados>';
        $msg .= '</cabecalho>';
        $msg .= '</nfseCabecMsg>';
        $msg .= '<nfseDadosMsg>';
        $msg .= $body;
        $msg .= '</nfseDadosMsg>';
        $msg .= '</nfse:RecepcionarLoteRps>';
        $msg .= '</soapenv:Body>';
        $msg .= '</soapenv:Envelope>';

        return $msg;
    }
}
