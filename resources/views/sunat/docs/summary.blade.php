<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' ?>
<SummaryDocuments xmlns="urn:sunat:names:specification:ubl:peru:schema:xsd:SummaryDocuments-1"
                  xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                  xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
                  xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                  xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
                  xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent/>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.0</cbc:UBLVersionID>
    <cbc:CustomizationID>1.1</cbc:CustomizationID>
    <cbc:ID>{{ $documento->idresumen }}</cbc:ID>
    <cbc:ReferenceDate>{{ $documento->fecha_emision_boletas }}</cbc:ReferenceDate>
    <cbc:IssueDate>{{ $documento->fecha_generacion_resumen }}</cbc:IssueDate>
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
    <sac:SummaryDocumentsLine>
        <cbc:LineID>{{$item->num_item}}</cbc:LineID>
        <cbc:DocumentTypeCode>{{$item->facturacion->codigo_tipo_documento}}</cbc:DocumentTypeCode>
        <cbc:ID>{{$item->facturacion->serie}}-{{$item->facturacion->correlativo}}</cbc:ID>
        <cac:AccountingCustomerParty>
            <cbc:CustomerAssignedAccountID>{{$item->ruc}}</cbc:CustomerAssignedAccountID>
            <cbc:AdditionalAccountID>{{$item->cliente->tipo_documento}}</cbc:AdditionalAccountID>
        </cac:AccountingCustomerParty>
        @if($item->facturacion->num_doc_relacionado)
        <cac:BillingReference>
            <cac:InvoiceDocumentReference>
                <cbc:ID>{{ $item->facturacion->num_doc_relacionado }}</cbc:ID>
                <cbc:DocumentTypeCode>{{ $item->facturacion->tipo_doc_relacionado }}</cbc:DocumentTypeCode>
            </cac:InvoiceDocumentReference>
        </cac:BillingReference>
        @endif
        @if($item->percepcion)
        <sac:SUNATPerceptionSummaryDocumentReference>
            <sac:SUNATPerceptionSystemCode>{{ $item->codigo_percepcion }}</sac:SUNATPerceptionSystemCode>
            <sac:SUNATPerceptionPercent>{{ $item->porcentaje_percepcion}}</sac:SUNATPerceptionPercent>
            <cbc:TotalInvoiceAmount currencyID="PEN">{{ $item->monto_percepcion }}</cbc:TotalInvoiceAmount>
            <sac:SUNATTotalCashed currencyID="PEN">{{ $item->total_mas_percepcion }}</sac:SUNATTotalCashed>
            <cbc:TaxableAmount currencyID="PEN">{{ $item->monto_base_percepcion }}</cbc:TaxableAmount>
        </sac:SUNATPerceptionSummaryDocumentReference>
        @endif
        <cac:Status>
            <cbc:ConditionCode>1</cbc:ConditionCode>
        </cac:Status>
        <sac:TotalAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{ $item->total_venta }}</sac:TotalAmount>
        @if($item->facturacion->total_gravadas)
            <sac:BillingPayment>
                <cbc:PaidAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{$item->facturacion->total_gravadas}}</cbc:PaidAmount>
                <cbc:InstructionID>01</cbc:InstructionID>
            </sac:BillingPayment>
        @endif
        @if($item->facturacion->total_exoneradas)
            <sac:BillingPayment>
                <cbc:PaidAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{$item->facturacion->total_exoneradas}}</cbc:PaidAmount>
                <cbc:InstructionID>02</cbc:InstructionID>
            </sac:BillingPayment>
        @endif
        @if($item->facturacion->total_inafectas)
            <sac:BillingPayment>
                <cbc:PaidAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{$item->facturacion->total_inafectas}}</cbc:PaidAmount>
                <cbc:InstructionID>03</cbc:InstructionID>
            </sac:BillingPayment>
        @endif
        @if($item->total_exportacion)
            <sac:BillingPayment>
                <cbc:PaidAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{$item->total_exportacion}}</cbc:PaidAmount>
                <cbc:InstructionID>04</cbc:InstructionID>
            </sac:BillingPayment>
        @endif
        @if($item->facturacion->total_gratuitas)
            <sac:BillingPayment>
                <cbc:PaidAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{$item->facturacion->total_gratuitas}}</cbc:PaidAmount>
                <cbc:InstructionID>05</cbc:InstructionID>
            </sac:BillingPayment>
        @endif
        @if($item->otros_cargos)
        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
            <cbc:Amount currencyID="{{ $item->facturacion->codigo_moneda }}">{{ $item->otros_cargos }}</cbc:Amount>
        </cac:AllowanceCharge>
        @endif
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{ $item->facturacion->igv }}</cbc:TaxAmount>
            <cac:TaxSubtotal>
                <cbc:TaxAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{ $item->facturacion->igv }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>1000</cbc:ID>
                        <cbc:Name>IGV</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        @if($item->facturacion->isc > '0.00')
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{ $item->facturacion->isc }}</cbc:TaxAmount>
            <cac:TaxSubtotal>
                <cbc:TaxAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{ $item->facturacion->isc }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>2000</cbc:ID>
                        <cbc:Name>ISC</cbc:Name>
                        <cbc:TaxTypeCode>EXC</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        @endif
        @if($item->otros_cargos)
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{ $item->otros_cargos }}</cbc:TaxAmount>
            <cac:TaxSubtotal>
                <cbc:TaxAmount currencyID="{{ $item->facturacion->codigo_moneda }}">{{ $item->otros_cargos }}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>7152</cbc:ID>
                        <cbc:Name>ICBPER</cbc:Name>
                        <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
        @endif
    </sac:SummaryDocumentsLine>
    @endforeach
</SummaryDocuments>
