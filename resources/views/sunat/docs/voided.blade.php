<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' ?>
<VoidedDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:VoidedDocuments-1"
                 xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                 xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
                 xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
                 xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1"
                 xmlns:ds="http://www.w3.org/2000/09/xmldsig#">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent/>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.0</cbc:UBLVersionID>
    <cbc:CustomizationID>1.0</cbc:CustomizationID>
    <cbc:ID>{{ $documento['idanulacion'] }}</cbc:ID>
    <cbc:ReferenceDate>{{ $documento['fecha_emision_documentos'] }}</cbc:ReferenceDate>
    <cbc:IssueDate>{{ $documento['fecha_generacion_anulacion'] }}</cbc:IssueDate>
    <cac:Signature>
        <cbc:ID>{{$emisor->ruc}}</cbc:ID>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>{{$emisor->ruc}}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>![CDATA[{{$emisor->razon_social}}]]</cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#coditecSign</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
        <cbc:CustomerAssignedAccountID>{{$emisor->ruc}}</cbc:CustomerAssignedAccountID>
        <cbc:AdditionalAccountID>{{$emisor->tipo_documento}}</cbc:AdditionalAccountID>
        <cac:Party>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{$emisor->razon_social}}]]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingSupplierParty>
    @foreach($items as $item)
    <sac:VoidedDocumentsLine>
        <cbc:LineID>{{$item['num_item']}}</cbc:LineID>
        <cbc:DocumentTypeCode>{{$item['codigo_tipo_documento']}}</cbc:DocumentTypeCode>
        <sac:DocumentSerialID>{{ $item['serie'] }}</sac:DocumentSerialID>
        <sac:DocumentNumberID>{{ $item['correlativo'] }}</sac:DocumentNumberID>
        <sac:VoidReasonDescription><![CDATA[{{ $item['motivo_baja']}}]]></sac:VoidReasonDescription>
    </sac:VoidedDocumentsLine>
    @endforeach
</VoidedDocuments>