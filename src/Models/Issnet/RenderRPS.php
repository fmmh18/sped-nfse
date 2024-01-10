<?php

namespace NFePHP\NFSe\Models\Issnet;

/**
 * Classe para a renderização dos RPS em XML
 * conforme o modelo ISSNET
 *
 * @category  NFePHP
 * @package   NFePHP\NFSe\Models\Issnet\RenderRPS
 * @copyright NFePHP Copyright (c) 2016
 * @license   http://www.gnu.org/licenses/lgpl.txt LGPLv3+
 * @license   https://opensource.org/licenses/MIT MIT
 * @license   http://www.gnu.org/licenses/gpl.txt GPLv3+
 * @author    Roberto L. Machado <linux.rlm at gmail dot com>
 * @link      http://github.com/nfephp-org/sped-nfse for the canonical source repository
 */

use DOMElement;
use NFePHP\Common\Certificate;
use NFePHP\Common\Certificate\PublicKey;
use NFePHP\Common\DOMImproved as Dom;

class RenderRPS
{
    /**
     * @var DOMImproved
     */
    protected static $dom;
    /**
     * @var Certificate
     */
    protected static $certificate;
    /**
     * @var int
     */
    protected static $algorithm;
    /**
     * @var \DateTimeZone
     */
    protected static $timezone;

    public static function toXml(
        $data,
        \DateTimeZone $timezone,
        $algorithm = OPENSSL_ALGO_SHA1,
        Certificate $certificate
    ) {
        self::$certificate = $certificate;
        self::$algorithm = $algorithm;
        self::$timezone = $timezone;
        $xml = '';
        if (is_object($data)) {
            return self::render($data, $algorithm, $certificate);
        } elseif (is_array($data)) {
            foreach ($data as $rps) {
                $xml .= self::render($rps, $algorithm, $certificate);
            }
        }
        return $xml;
    }

    /**
     * Monta o xml com base no objeto Rps
     * @param Rps $rps
     * @return string
     */
    public static function removerCaracteresEspeciais($str)
    {
        // Substituir caracteres especiais por uma string vazia
        $str = preg_replace('/[^a-zA-Z0-9]/', '', $str);

        // Retornar a string sem caracteres especiais
        return $str;
    }
    private static function render(
        Rps $rps,
        $algorithm,
        $certificate
    ) {

        self::$dom = new Dom('1.0', 'utf-8');
        $root = self::$dom->createElement('Rps');
        $infRPS = self::$dom->createElement('InfDeclaracaoPrestacaoServico');
        $rpsClild = self::$dom->createElement('Rps');
        $identificacaoRps = self::$dom->createElement('IdentificacaoRps');
        self::$dom->addChild(
            $identificacaoRps,
            'Numero',
            $rps->infNumero,
            true,
            "Numero do RPS",
            true
        );
        self::$dom->addChild(
            $identificacaoRps,
            'Serie',
            $rps->infSerie,
            true,
            "Serie do RPS",
            true
        );
        self::$dom->addChild(
            $identificacaoRps,
            'Tipo',
            $rps->infTipo,
            true,
            "Tipo do RPS",
            true
        );
        self::$dom->appChild($rpsClild, $identificacaoRps, 'Adicionando tag IdentificacaoRPS');
        $rps->infDataEmissao->setTimezone(self::$timezone);
        self::$dom->addChild(
            $rpsClild,
            'DataEmissao',
            $rps->infDataEmissao->format('Y-m-d'),
            true,
            'Data de Emissão do RPS',
            false
        );
        self::$dom->addChild(
            $rpsClild,
            'Status',
            $rps->infStatus,
            true,
            'Status',
            false
        );

        self::$dom->appChild($infRPS, $rpsClild, 'Adicionando tag RPS em infRPS');

        if (!empty($rps->infRpsSubstituido['numero'])) {
            $rpssubs = self::$dom->createElement('RpsSubstituido');
            self::$dom->addChild(
                $rpssubs,
                'Numero',
                $rps->infRpsSubstituido['numero'],
                true,
                'Numero',
                false
            );
            self::$dom->addChild(
                $rpssubs,
                'Serie',
                $rps->infRpsSubstituido['serie'],
                true,
                'Serie',
                false
            );
            self::$dom->addChild(
                $rpssubs,
                'Tipo',
                $rps->infRpsSubstituido['tipo'],
                true,
                'tipo',
                false
            );
            self::$dom->appChild($infRPS, $rpssubs, 'Adicionando tag RpsSubstituido em infRps');
        }

        self::$dom->addChild(
            $infRPS,
            'Competencia',
            $rps->infDataEmissao->format('Y-m-d'),
            true,
            'Competencia',
            false
        );
        $servico = self::$dom->createElement('Servico');
        $valores = self::$dom->createElement('Valores');
        self::$dom->addChild(
            $valores,
            'ValorServicos',
            $rps->infValorServicos,
            true,
            'ValorServicos',
            false
        );
        if($rps->infOptanteSimplesNacional == 1){
            self::$dom->addChild(
                $valores,
                'ValorIss',
                $rps->infValorIss,
                false,
                'ValorIss',
                false
            );
            self::$dom->addChild(
                $valores,
                'Aliquota',
                number_format($rps->infAliquota, 2, '.', ''),
                false,
                'Aliquota',
                false
            );
        }
        self::$dom->appChild($servico, $valores, 'Adicionando tag Valores em Servico');
        self::$dom->addChild(
            $servico,
            'IssRetido',
            $rps->infIssRetido,
            true,
            'IssRetido',
            false
        );
        self::$dom->addChild(
            $servico,
            'ItemListaServico',
            $rps->infItemListaServico,
            true,
            'ItemListaServico',
            false
        );
        self::$dom->addChild(
            $servico,
            'CodigoCnae',
            $rps->infCodigoCnae,
            true,
            'CodigoCnae',
            false
        );
        self::$dom->addChild(
            $servico,
            'CodigoTributacaoMunicipio',
            $rps->infCodigoTributacaoMunicipio,
            true,
            'CodigoTributacaoMunicipio',
            false
        );
        self::$dom->addChild(
            $servico,
            'Discriminacao',
            self::removerCaracteresEspeciais($rps->infDiscriminacao),
            true,
            'Discriminacao',
            false
        );
        self::$dom->addChild(
            $servico,
            'CodigoMunicipio',
            $rps->infMunicipioPrestacaoServico,
            true,
            'CodigoMunicipio',
            false
        );
        self::$dom->addChild(
            $servico,
            'ExigibilidadeISS',
            1,
            true,
            'ExigibilidadeISS',
            false
        );
        self::$dom->addChild(
            $servico,
            'MunicipioIncidencia',
            $rps->infMunicipioPrestacaoServico,
            true,
            'MunicipioIncidencia',
            false
        );
        self::$dom->appChild($infRPS, $servico, 'Adicionando tag Servico');

        $prestador = self::$dom->createElement('Prestador');
        $cpfCnpj = self::$dom->createElement('CpfCnpj');
        if ($rps->infPrestador['tipo'] == 2) {
            self::$dom->addChild(
                $cpfCnpj,
                'Cnpj',
                $rps->infPrestador['cnpjcpf'],
                true,
                'Prestador CNPJ',
                false
            );
        } else {
            self::$dom->addChild(
                $cpfCnpj,
                'Cpf',
                $rps->infPrestador['cnpjcpf'],
                true,
                'Prestador CPF',
                false
            );
        }
        self::$dom->appChild($prestador, $cpfCnpj, 'Adicionando tag CpfCnpj em Prestador');
        self::$dom->addChild(
            $prestador,
            'InscricaoMunicipal',
            $rps->infPrestador['im'],
            true,
            'InscricaoMunicipal',
            false
        );
        self::$dom->appChild($infRPS, $prestador, 'Adicionando tag Prestador em infRPS');

        $tomador = self::$dom->createElement('TomadorServico');
        $identificacaoTomador = self::$dom->createElement('IdentificacaoTomador');
        $cpfCnpjTomador = self::$dom->createElement('CpfCnpj');
        if ($rps->infTomador['tipo'] == 2) {
            self::$dom->addChild(
                $cpfCnpjTomador,
                'Cnpj',
                $rps->infTomador['cnpjcpf'],
                true,
                'Tomador CNPJ',
                false
            );
        } else {
            self::$dom->addChild(
                $cpfCnpjTomador,
                'Cpf',
                $rps->infTomador['cnpjcpf'],
                true,
                'Tomador CPF',
                false
            );
        }
        self::$dom->appChild($identificacaoTomador, $cpfCnpjTomador, 'Adicionando tag CpfCnpj em IdentificacaTomador');
        self::$dom->appChild($tomador, $identificacaoTomador, 'Adicionando tag IdentificacaoTomador em Tomador');
        self::$dom->addChild(
            $tomador,
            'RazaoSocial',
            $rps->infTomador['razao'],
            true,
            'RazaoSocial',
            false
        );
        $endereco = self::$dom->createElement('Endereco');
        self::$dom->addChild(
            $endereco,
            'Endereco',
            $rps->infTomadorEndereco['end'],
            true,
            'Endereco',
            false
        );
        self::$dom->addChild(
            $endereco,
            'Numero',
            $rps->infTomadorEndereco['numero'],
            true,
            'Numero',
            false
        );
        if (!empty($rps->infTomadorEndereco['complemento'])) {
            self::$dom->addChild(
                $endereco,
                'Complemento',
                $rps->infTomadorEndereco['complemento'],
                true,
                'Complemento',
                false
            );
        }
        self::$dom->addChild(
            $endereco,
            'Bairro',
            $rps->infTomadorEndereco['bairro'],
            true,
            'Bairro',
            false
        );
        self::$dom->addChild(
            $endereco,
            'CodigoMunicipio',
            $rps->infTomadorEndereco['cmun'],
            true,
            'CodigoMunicipio',
            false
        );
        self::$dom->addChild(
            $endereco,
            'Uf',
            $rps->infTomadorEndereco['uf'],
            true,
            'Uf',
            false
        );
        self::$dom->addChild(
            $endereco,
            'Cep',
            $rps->infTomadorEndereco['cep'],
            true,
            'Cep',
            false
        );
        self::$dom->appChild($tomador, $endereco, 'Adicionando tag Endereco em Tomador');

        if ($rps->infTomador['tel'] != '' || $rps->infTomador['email'] != '') {
            $contato = self::$dom->createElement('Contato');
            self::$dom->addChild(
                $contato,
                'Telefone',
                str_replace(" ", "", $rps->infTomador['tel']),
                false,
                'Telefone Tomador',
                false
            );
            self::$dom->addChild(
                $contato,
                'Email',
                $rps->infTomador['email'],
                false,
                'Email Tomador',
                false
            );
            self::$dom->appChild($tomador, $contato, 'Adicionando tag Contato em Tomador');
        }
        self::$dom->appChild($infRPS, $tomador, 'Adicionando tag Tomador em infRPS');

        if (!empty($rps->infIntermediario['razao'])) {
            $intermediario = self::$dom->createElement('IntermediarioServico');
            self::$dom->addChild(
                $intermediario,
                'RazaoSocial',
                $rps->infIntermediario['razao'],
                true,
                'Razao Intermediario',
                false
            );
            $cpfCnpj = self::$dom->createElement('CpfCnpj');
            if ($rps->infIntermediario['tipo'] == 2) {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cnpj',
                    $rps->infIntermediario['cnpjcpf'],
                    true,
                    'CNPJ Intermediario',
                    false
                );
            } elseif ($rps->infIntermediario['tipo'] == 1) {
                self::$dom->addChild(
                    $cpfCnpj,
                    'Cpf',
                    $rps->infIntermediario['cnpjcpf'],
                    true,
                    'CPF Intermediario',
                    false
                );
            }
            self::$dom->appChild($intermediario, $cpfCnpj, 'Adicionando tag CpfCnpj em Intermediario');
            self::$dom->addChild(
                $intermediario,
                'InscricaoMunicipal',
                $rps->infIntermediario['im'],
                false,
                'IM Intermediario',
                false
            );
            self::$dom->appChild($infRPS, $intermediario, 'Adicionando tag Intermediario em infRPS');
        }
        if (!empty($rps->infConstrucaoCivil['obra'])) {
            $construcao = self::$dom->createElement('ContrucaoCivil');
            self::$dom->addChild(
                $construcao,
                'CodigoObra',
                $rps->infConstrucaoCivil['obra'],
                true,
                'Codigo da Obra',
                false
            );
            self::$dom->addChild(
                $construcao,
                'Art',
                $rps->infConstrucaoCivil['art'],
                true,
                'Art da Obra',
                false
            );
            self::$dom->appChild($infRPS, $construcao, 'Adicionando tag Construcao em infRPS');
        }
        self::$dom->addChild(
            $infRPS,
            'OptanteSimplesNacional',
            $rps->infOptanteSimplesNacional,
            true,
            'OptanteSimplesNacional',
            false
        );
        self::$dom->addChild(
            $infRPS,
            'IncentivoFiscal',
            2,
            true,
            'IncentivoFiscal',
            false
        );

        self::$dom->appChild($root, $infRPS, 'Adicionando tag infRPS em RPS');

        self::$dom->appendChild($root);
        $xml = str_replace('<?xml version="1.0" encoding="utf-8"?>', '', self::$dom->saveXML());
        return $xml;
    }

}
