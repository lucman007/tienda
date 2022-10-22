<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' ?>
<DebitNote xmlns="urn:oasis:names:specification:ubl:schema:xsd:DebitNote-2"
           xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
           xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
           xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
           xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent/>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>{{$documento->serie}}-{{$documento->correlativo}}</cbc:ID>
    <cbc:IssueDate>{{$documento->fecha_emision}}</cbc:IssueDate>
    <cbc:IssueTime>{{$documento->hora_emision}}</cbc:IssueTime>
    <cbc:Note languageLocaleID="{{$documento->codigo_leyenda}}">{{$documento->leyenda}}</cbc:Note>
    <cbc:DocumentCurrencyCode>{{$documento->codigo_moneda}}</cbc:DocumentCurrencyCode>
    <cac:DiscrepancyResponse>
        <cbc:ReferenceID>{{$documento->facturacion->num_doc_relacionado}}</cbc:ReferenceID>
        <cbc:ResponseCode>{{$documento->facturacion->tipo_nota_electronica}}</cbc:ResponseCode>
        <cbc:Description> {{$documento->facturacion->descripcion_nota}}</cbc:Description>
    </cac:DiscrepancyResponse>
    @if($documento->facturacion->oc_relacionada )
        <cac:OrderReference>
            <cbc:ID>{{ $documento->facturacion->oc_relacionada }}</cbc:ID>
        </cac:OrderReference>
    @endif
    <cac:BillingReference>
        <cac:InvoiceDocumentReference>
            <cbc:ID>{{$documento->facturacion->num_doc_relacionado}}</cbc:ID>
            <cbc:DocumentTypeCode>{{$documento->facturacion->tipo_doc_relacionado}}</cbc:DocumentTypeCode>
        </cac:InvoiceDocumentReference>
    </cac:BillingReference>
    @if($documento->facturacion->guia_relacionada)
        <cac:DespatchDocumentReference>
            <cbc:ID>{{ $documento->facturacion->guia_relacionada }}</cbc:ID>
            <cbc:DocumentTypeCode>{{ $documento->tipo_guia }}</cbc:DocumentTypeCode>
        </cac:DespatchDocumentReference>
    @endif
    @if($documento->otro_doc_relacionado)
        <cac:AdditionalDocumentReference>
            <cbc:ID>{{$documento->facturacion->num_doc_relacionado}}</cbc:ID>
            <cbc:DocumentTypeCode>{{$documento->facturacion->tipo_doc_relacionado}}</cbc:DocumentTypeCode>
        </cac:AdditionalDocumentReference>
    @endif
    <cac:Signature>
        <cbc:ID>coditecSign</cbc:ID>
        <cac:SignatoryParty>
            <cac:PartyIdentification>
                <cbc:ID>{{$emisor->ruc}}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyName>
                <cbc:Name>{{$emisor->razon_social}}</cbc:Name>
            </cac:PartyName>
        </cac:SignatoryParty>
        <cac:DigitalSignatureAttachment>
            <cac:ExternalReference>
                <cbc:URI>#coditecSign</cbc:URI>
            </cac:ExternalReference>
        </cac:DigitalSignatureAttachment>
    </cac:Signature>
    <cac:AccountingSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="{{$emisor->tipo_documento}}">{{$emisor->ruc}}</cbc:ID>
            </cac:PartyIdentification>
            @if($emisor->nombre_comercial)
                <cac:PartyName>
                    <cbc:Name><![CDATA[{{$emisor->nombre_comercial}}]]>></cbc:Name>
                </cac:PartyName>
            @endif
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{$emisor->razon_social}}]]></cbc:RegistrationName>
                <cac:RegistrationAddress>
                    <cbc:ID>{{$emisor->ubigeo}}</cbc:ID>
                    <cbc:AddressTypeCode>0000</cbc:AddressTypeCode>
                    <cbc:CityName>{{$emisor->provincia}}</cbc:CityName>
                    <cbc:CountrySubentity>{{$emisor->departamento}}</cbc:CountrySubentity>
                    <cbc:District>{{$emisor->distrito}}</cbc:District>
                    <cac:Country>
                        <cbc:IdentificationCode>{{$emisor->codigo_pais}}</cbc:IdentificationCode>
                    </cac:Country>
                </cac:RegistrationAddress>
            </cac:PartyLegalEntity>
            <cac:Contact>
                <cbc:Telephone>{{$emisor->telefono_1}}</cbc:Telephone>
                <cbc:ElectronicMail>{{$emisor->email}}</cbc:ElectronicMail>
            </cac:Contact>
        </cac:Party>
    </cac:AccountingSupplierParty>
    <cac:AccountingCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="{{ $usuario->tipo_documento }}">{{ $usuario->num_documento }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ $usuario->razon_social }}]]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    @if($documento->facturacion->descuento_global>'0.00')
        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode>00</cbc:AllowanceChargeReasonCode>
            <cbc:MultiplierFactorNumeric>{{$documento->facturacion->porcentaje_descuento_global}}</cbc:MultiplierFactorNumeric>
            <cbc:Amount
                    currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->descuento_global}}</cbc:Amount>
            <cbc:BaseAmount
                    currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->base_descuento_global}}</cbc:BaseAmount>
        </cac:AllowanceCharge>
    @endif
    <cac:TaxTotal>
        <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->total_impuestos}}</cbc:TaxAmount>
        @if($documento->facturacion->igv>'0.00')
            <cac:TaxSubtotal>
                <cbc:TaxableAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->total_gravadas}}</cbc:TaxableAmount>
                <cbc:TaxAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->igv}}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:ID>S
                    </cbc:ID>
                    <cac:TaxScheme>
                        <cbc:ID>1000</cbc:ID>
                        <cbc:Name>IGV</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        @endif
        @if($documento->facturacion->isc>'0.00')
            <cac:TaxSubtotal>
                <cbc:TaxableAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->total_gravadas_isc}}</cbc:TaxableAmount>
                <cbc:TaxAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->isc}}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:ID>S
                    </cbc:ID>
                    <cac:TaxScheme>
                        <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">2000</cbc:ID>
                        <cbc:Name>ISC</cbc:Name>
                        <cbc:TaxTypeCode>EXC</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        @endif
        @if($documento->facturacion->total_inafectas>'0.00')
            <cac:TaxSubtotal>
                <cbc:TaxableAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->total_inafectas}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">0.00</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:ID>O
                    </cbc:ID>
                    <cac:TaxScheme>
                        <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9998</cbc:ID>
                        <cbc:Name>INA</cbc:Name>
                        <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        @endif
        @if($documento->facturacion->total_exoneradas>'0.00')
            <cac:TaxSubtotal>
                <cbc:TaxableAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->total_exoneradas}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">0.00</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:ID>E
                    </cbc:ID>
                    <cac:TaxScheme>
                        <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9997</cbc:ID>
                        <cbc:Name>EXO</cbc:Name>
                        <cbc:TaxTypeCode>VAT</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        @endif
        @if($documento->facturacion->total_gratuitas>'0.00')
            <cac:TaxSubtotal>
                <cbc:TaxableAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->total_gratuitas}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">0.00</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:ID>Z
                    </cbc:ID>
                    <cac:TaxScheme>
                        <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9996</cbc:ID>
                        <cbc:Name>GRA</cbc:Name>
                        <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        @endif
        @if($documento->otros_cargos)
            <cac:TaxSubtotal>
                <cbc:TaxableAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->total_monto_otros}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->otros_cargos}}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:ID>S
                    </cbc:ID>
                    <cac:TaxScheme>
                        <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">9999</cbc:ID>
                        <cbc:Name>OTR</cbc:Name>
                        <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        @endif
        @if($documento->icbper)
            <cac:TaxSubtotal>
                <cbc:TaxableAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->total_monto_icbper}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->icbper}}</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cbc:ID>S
                    </cbc:ID>
                    <cac:TaxScheme>
                        <cbc:ID schemeID="UN/ECE 5153" schemeAgencyID="6">7152</cbc:ID>
                        <cbc:Name>ICBPER</cbc:Name>
                        <cbc:TaxTypeCode>OTH</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        @endif
    </cac:TaxTotal>
    <cac:RequestedMonetaryTotal>
        <cbc:PayableAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->total_venta}}</cbc:PayableAmount>
    </cac:RequestedMonetaryTotal>
    @foreach($items as $item)
        <cac:DebitNoteLine>
            <cbc:ID>{{$item->num_item}}</cbc:ID>
            <cbc:DebitedQuantity unitCode="{{$item->codigo_medida}}">{{$item->cantidad}}</cbc:DebitedQuantity>
            <cbc:LineExtensionAmount
                    currencyID="{{$documento->codigo_moneda}}">{{$item->valor_venta_unitario_por_item}}</cbc:LineExtensionAmount>
            <cac:PricingReference>
                <cac:AlternativeConditionPrice>
                    <cbc:PriceAmount
                            currencyID="{{$documento->codigo_moneda}}">{{$item->valor_referencial}}</cbc:PriceAmount>
                    <cbc:PriceTypeCode>{{$item->tipo_precio_venta_unitario_por_item}}</cbc:PriceTypeCode>
                </cac:AlternativeConditionPrice>
            </cac:PricingReference>
            @if($item->cargos)
                <cac:AllowanceCharge>
                    <cbc:ChargeIndicator>true</cbc:ChargeIndicator>
                    <cbc:AllowanceChargeReasonCode>00</cbc:AllowanceChargeReasonCode>
                    <cbc:MultiplierFactorNumeric>0.10</cbc:MultiplierFactorNumeric>
                    <cbc:Amount currencyID="{{$documento->codigo_moneda}}">16610.17</cbc:Amount>
                    <cbc:BaseAmount currencyID="{{$documento->codigo_moneda}}">166101.69</cbc:BaseAmount>
                </cac:AllowanceCharge>
            @endif
            @if($item->detalle->descuento>'0.00')
                <cac:AllowanceCharge>
                    <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
                    <cbc:AllowanceChargeReasonCode>00</cbc:AllowanceChargeReasonCode>
                    <cbc:MultiplierFactorNumeric>{{$item->detalle->porcentaje_descuento/100}}</cbc:MultiplierFactorNumeric>
                    <cbc:Amount currencyID="{{$documento->codigo_moneda}}">{{$item->detalle->descuento}}</cbc:Amount>
                    <cbc:BaseAmount
                            currencyID="{{$documento->codigo_moneda}}">{{$item->base_descuento}}</cbc:BaseAmount>
                </cac:AllowanceCharge>
            @endif
            <cac:TaxTotal>
                <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">{{$item->igv}}</cbc:TaxAmount>
                <cac:TaxSubtotal>
                    <cbc:TaxableAmount
                            currencyID="{{$documento->codigo_moneda}}">{{$item->detalle->subtotal}}</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">{{$item->igv}}</cbc:TaxAmount>
                    <cac:TaxCategory>
                        <cbc:Percent>{{$item->porcentaje_igv}}</cbc:Percent>
                        <cbc:TaxExemptionReasonCode>{{$item->tipo_afectacion_igv}}</cbc:TaxExemptionReasonCode>
                        <cac:TaxScheme>
                            <cbc:ID>{{$item->tax_code}}</cbc:ID>
                            <cbc:Name>{{$item->tax_siglas}}</cbc:Name>
                            <cbc:TaxTypeCode>{{$item->tax_name_code}}</cbc:TaxTypeCode>
                        </cac:TaxScheme>
                    </cac:TaxCategory>
                </cac:TaxSubtotal>
            </cac:TaxTotal>
            <cac:Item>
                <cbc:Description><![CDATA[{{preg_replace("/[\r\n|\n|\r]+/", " ",strip_tags($item->descripcion))}}]]></cbc:Description>
                <cac:SellersItemIdentification>
                    <cbc:ID>{{$item->codigo}}</cbc:ID>
                </cac:SellersItemIdentification>
                @if($item->codigo_producto_sunat)
                    <cac:CommodityClassification>
                        <cbc:ItemClassificationCode>{{$item->codigo_producto_sunat}}</cbc:ItemClassificationCode>
                    </cac:CommodityClassification>
                @endif
            </cac:Item>
            @if($item->tipo_precio_venta_unitario_por_item==02)
                <cac:Price>
                    <cbc:PriceAmount
                            currencyID="{{$documento->codigo_moneda}}">0.00
                    </cbc:PriceAmount>
                </cac:Price>
            @else
                <cac:Price>
                    <cbc:PriceAmount
                            currencyID="{{$documento->codigo_moneda}}">{{$item->valor_venta_bruto_unitario}}</cbc:PriceAmount>
                </cac:Price>
            @endif
        </cac:DebitNoteLine>
    @endforeach
</DebitNote>