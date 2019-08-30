	2019_aleg_rel03.txt
	2019_aleg_rel03.txt.matricula.txt

## Importar dados de pagamento (Preparo de Pagamento)
#### Formato

```
     MATRICULA                 NOME                  UADM       CPF               CARGO                      FUNCAO               REND.FUNC       COMISSAO       REPRESENT.     INCORPORADO       TRIENIO          ABONO           FERIAS          REDUTOR      PREVIDENCIA        IR         TOTAL LIQUIDO
0     000.000-8 TESTE DE NOME XXXXX                  440272  7140842710                           ASSESSOR PARLAMENTAR VII                              843,05      2.107,61                                                                                          158,34           0,00         1.601,06
```

#### Comando
```
tce:import:pagamento [year] [month] [input-file]
```

## Importar dados de pessoal (DISI gerou arquivo) 
#### Formato
```
MATRICULA;CPF;NOME;DATA_CESSAO;ADMISSAO /REQUISICAO /NOMEACAO;APOSENTADORIA /RETORNO /EXONERACAO;DESCRICAO;ORGAO_CESSAO;MUNICIPIO_ORG_CESSAO;CEDIDO_PARA
200XXX;3693653XXXX;TESTE XXXXX;;01/08/1976;;EFETIVO ATIVO;;;
```

#### Comando
```
tce:import:pessoal [year] [month] [input-file]
```

## Gerar relatório final
```
tce:report [output-file]            
```

API de Acesso
http://intrahom2008/SARH/eTCEWebApi/Help
http://intrahom2008/SARH/eTCEWebApi/ConsultarFuncionarioPeriodo/values?matricula=201794&dataInicial=01/05/2019&dataFinal=31/05/2019
