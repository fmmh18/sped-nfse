<?php

namespace NFePHP\NFSe\Models\Issnet\Factories;

class ConsultarLoteRps extends Factory
{
    public function render(
        $versao,
        $remetenteTipoDoc,
        $remetenteCNPJCPF,
        $inscricaoMunicipal,
        $protocolo
    ) {
        $method = "ConsultarLoteRpsEnvio";
        $content = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:nfse="http://nfse.abrasf.org.br">';
        $content .= '<soapenv:Body>';
	    $content .= '<nfse:ConsultarLoteRps>';
	  	$content .= '<nfseCabecMsg>';
		$content .= '<cabecalho xmlns="http://www.abrasf.org.br/nfse.xsd" versao="2.04">';
		$content .= '<versaoDados>2.04</versaoDados>';
		$content .= '</cabecalho>';
		$content .= '</nfseCabecMsg>';
		$content .= '<nfseDadosMsg>';
		$content .= '<ConsultarLoteRpsEnvio xmlns="http://www.abrasf.org.br/nfse.xsd">';
		$content .= '<Prestador>';
		$content .= '<CpfCnpj>';
		$content .= '<Cnpj>'.$remetenteCNPJCPF.'</Cnpj>';
		$content .= '</CpfCnpj>';
		$content .= '<InscricaoMunicipal>'.$inscricaoMunicipal.'</InscricaoMunicipal>';
		$content .= '</Prestador>';
		$content .= '<Protocolo>'.$protocolo.'</Protocolo>';
		$content .= '</ConsultarLoteRpsEnvio>';
      	$content .= '</nfseDadosMsg>';
	    $content .= '</nfse:ConsultarLoteRps>';
        $content .= '</soapenv:Body>';
        $content .= '</soapenv:Envelope>';
        $body = $this->clear($content);
        return $body;
    }
}
