<?php
// php extract dump configuration file
$config = array(
        'mysqli' => array('192.18.0.146','infomed','lbc02bio','mv_repl'),
        'prefix' => array(':' =>'http://cipe.accamargo.org.br/ontologias/exemplo_protocolo_tev.owl#' ),
        'maps'=>array(
                array(
                        'sql' => "

select
          cd_paciente
        , atendime.cd_atendimento
        , dt_atendimento
        , dt_registro
        , DATE_FORMAT(dt_registro, '%Y-%m-%dT%TZ') as dt_registro_xml
        , ds_pergunta
        , ds_resposta
        , case when ds_resposta = 'checked' then 'ProtocoloProfilaxiaTEVComRecomendacaoDeMeiaElastica' else 'ProtocoloProfilaxiaTEV' end as protocolo_classe
from
        mv_repl.registro_documento
join
        mv_repl.atendime on (atendime.cd_atendimento = registro_documento.cd_atendimento)
join
        mv_repl.registro_resposta on ( registro_resposta.cd_registro_documento = registro_documento.cd_registro_documento )
join
        mv_repl.pergunta_doc on ( pergunta_doc.cd_pergunta_doc = registro_resposta.cd_pergunta_doc and ds_pergunta = 'CKB1')
where
        cd_documento = 10850 and dt_registro >= '2017-03-20'

",
                        'rdf' => '

:protocolo_{cd_atendimento} a :{protocolo_classe}.
:protocolo_{cd_atendimento} rdfs:comment "cd_atendime: {cd_atendimento}, dt_doc:{dt_registro}".
:protocolo_{cd_atendimento} :temEtapaInicial :protocolo_{cd_atendimento}_E0.

:protocolo_{cd_atendimento}_E0 a :AvaliaçãoDeRiscoTEV.
:protocolo_{cd_atendimento}_E0 rdfs:comment "meia recomendada:{ds_resposta}".
:protocolo_{cd_atendimento}_E0 <http://purl.org/dc/elements/1.1/date> "{dt_registro_xml}".


'
                ), 
                array(
                        'sql' => "
                            select    dt_pre_med
                                    , cd_pre_med
                                    , dt_pre_med_xml
                                    , cd_atendimento
                                    , @row_number:=CASE
                                            WHEN @cd_atendimento = cd_atendimento THEN @row_number + 1
                                            ELSE 1
                                            END AS rank
                                    , ( @row_number - 1 ) as prev
                                    , @cd_atendimento := cd_atendimento  as cd_atendimento
                            FROM (
                                    
                                    select
                                          dt_pre_med
                                        , pre_med.cd_pre_med
                                        , DATE_FORMAT(dt_pre_med, '%Y-%m-%dT%TZ') as dt_pre_med_xml
                                        , pre_med.cd_atendimento
                                    
                                    from mv_repl.pre_med
                                    join (select @row_number:=0,@cd_atendimento:=0 ) reset
                                    join mv_repl.itpre_med on ( itpre_med.cd_pre_med = pre_med.cd_pre_med )
                                    join mv_repl.tip_presc on ( tip_presc.cd_tip_presc = itpre_med.cd_tip_presc )
                                    
                                    join
                                        mv_repl.registro_documento on ( registro_documento.cd_atendimento = pre_med.cd_atendimento )
                                    join
                                        mv_repl.registro_resposta on ( registro_resposta.cd_registro_documento = registro_documento.cd_registro_documento )
                                    join
                                        mv_repl.pergunta_doc on ( pergunta_doc.cd_pergunta_doc = registro_resposta.cd_pergunta_doc and ds_pergunta = 'CKB1')
                                    
                                    
                                    where
                                        itpre_med.cd_tip_presc in ( 47473,40919,40921,40923,40924,40925,40922,33504,33505,40926,40918,40920,49082 )
                                    and (
                                        cd_documento = 10850 and dt_registro >= '2017-03-20' and ds_resposta = 'checked' and dt_pre_med >= dt_registro
                                        )
                                    
                                    order by 
                                                cd_atendimento,dt_pre_med asc
                            ) x
",
                        'rdf' => '
:protocolo_{cd_atendimento}_E{prev} :temProximaEtapa :protocolo_{cd_atendimento}_E{rank}.

:protocolo_{cd_atendimento}_E{rank} a :PrescriçãoMeiaElástica.
:protocolo_{cd_atendimento}_E{rank} rdfs:comment "dt_prescr:{dt_pre_med} order: {rank} prev: {prev} cd_atendimento:{cd_atendimento} cd_pre_med:{cd_pre_med}".
:protocolo_{cd_atendimento}_E{rank} <http://purl.org/dc/elements/1.1/date> "{dt_pre_med_xml}".
'
                )
        )
);



