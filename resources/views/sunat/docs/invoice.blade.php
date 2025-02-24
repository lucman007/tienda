<?php echo '<?xml version="1.0" encoding="UTF-8" standalone="no"?>' ?>
<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2"
         xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
         xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
         xmlns:ccts="urn:un:unece:uncefact:documentation:2" xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
         xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2"
         xmlns:qdt="urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2"
         xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1"
         xmlns:tci="http://tempuri.org/"
         xmlns:udt="urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2"
         xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
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
    @if($documento->tipo_pago==2)
        <cbc:DueDate>{{$documento->fecha_vencimiento}}</cbc:DueDate>
    @endif
    <cbc:InvoiceTypeCode listID="{{$documento->codigo_tipo_factura}}">{{$documento->codigo_tipo_documento}}</cbc:InvoiceTypeCode>
    <cbc:Note languageLocaleID="{{$documento->codigo_leyenda}}">{{$documento->leyenda}}</cbc:Note>
    @if($documento->facturacion->codigo_tipo_factura == '1001')
        <cbc:Note languageLocaleID="2006"><![CDATA[Operación sujeta a detracción]]></cbc:Note>
    @endif
    <cbc:DocumentCurrencyCode>{{$documento->codigo_moneda}}</cbc:DocumentCurrencyCode>
    @if($documento->codigo_tipo_documento==01)
    <cbc:LineCountNumeric>{{count($items)}}</cbc:LineCountNumeric>
    @endif
    @if($documento->facturacion->oc_relacionada )
        <cac:OrderReference>
            <cbc:ID>{{ $documento->facturacion->oc_relacionada }}</cbc:ID>
        </cac:OrderReference>
    @endif
    @if($documento->facturacion->guia_relacionada)
        <cac:DespatchDocumentReference>
            <cbc:ID>{{ $documento->facturacion->guia_relacionada }}</cbc:ID>
            <cbc:DocumentTypeCode>{{ $documento->tipo_guia }}</cbc:DocumentTypeCode>
        </cac:DespatchDocumentReference>
    @endif
    @if($documento->facturacion->guia_fisica)
        @foreach($documento->guias_fisicas as $guia)
        <cac:DespatchDocumentReference>
            <cbc:ID>{{ $guia }}</cbc:ID>
            <cbc:DocumentTypeCode>{{ $documento->tipo_guia }}</cbc:DocumentTypeCode>
        </cac:DespatchDocumentReference>
        @endforeach
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
                    <cbc:AddressTypeCode>{{$emisor->codigo_establecimiento}}</cbc:AddressTypeCode>
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
                <cbc:ID schemeID="{{ $documento->codigo_tipo_factura == '0200'?0:$usuario->tipo_documento}}">{{ $usuario->num_documento }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ $usuario->razon_social }}]]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:AccountingCustomerParty>
    @if($documento->facturacion->codigo_tipo_factura == '1001')
        <cac:PaymentMeans>
            <cbc:ID>Detraccion</cbc:ID>
            <cbc:PaymentMeansCode>001</cbc:PaymentMeansCode>
            <cac:PayeeFinancialAccount>
                <cbc:ID>{{$emisor->cuentas[0]['cuenta']}}</cbc:ID>
            </cac:PayeeFinancialAccount>
        </cac:PaymentMeans>
        <cac:PaymentTerms>
            <cbc:ID>Detraccion</cbc:ID>
            <cbc:PaymentMeansID>{{$documento->tipo_detraccion}}</cbc:PaymentMeansID>
            <cbc:PaymentPercent>{{$documento->porcentaje_detraccion}}</cbc:PaymentPercent>
            <cbc:Amount currencyID="PEN">{{$documento->detraccion}}</cbc:Amount>
        </cac:PaymentTerms>
    @endif
    @if($documento->tipo_pago==2)
        <cac:PaymentTerms>
            <cbc:ID>FormaPago</cbc:ID>
            <cbc:PaymentMeansID>Credito</cbc:PaymentMeansID>
            @if($documento->facturacion->codigo_tipo_factura == '1001')
                <cbc:Amount currencyID="{{$documento->codigo_moneda}}">{{$documento->monto_menos_detraccion}}</cbc:Amount>
                @elseif($documento->facturacion->retencion == 1)
                <cbc:Amount currencyID="{{$documento->codigo_moneda}}">{{$documento->monto_menos_retencion}}</cbc:Amount>
                @else
                <cbc:Amount currencyID="{{$documento->codigo_moneda}}">{{$documento->total_venta}}</cbc:Amount>
            @endif
        </cac:PaymentTerms>
        @php
            $i=1
        @endphp
        @foreach($documento->pago as $pago)
            <cac:PaymentTerms>
                <cbc:ID>FormaPago</cbc:ID>
                <cbc:PaymentMeansID>Cuota{{str_pad($i,3,"0",STR_PAD_LEFT)}}</cbc:PaymentMeansID>
                <cbc:Amount currencyID="{{$documento->codigo_moneda}}">{{$pago->monto}}</cbc:Amount>
                <cbc:PaymentDueDate>{{date('Y-m-d',strtotime($pago->fecha))}}</cbc:PaymentDueDate>
            </cac:PaymentTerms>
            @php
                $i++
            @endphp
        @endforeach
    @else
        <cac:PaymentTerms>
            <cbc:ID>FormaPago</cbc:ID>
            <cbc:PaymentMeansID>Contado</cbc:PaymentMeansID>
        </cac:PaymentTerms>
    @endif
    @if($documento->facturacion->retencion == 1)
        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode>62</cbc:AllowanceChargeReasonCode>
            <cbc:MultiplierFactorNumeric>0.03</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID="{{$documento->codigo_moneda}}">{{$documento->retencion}}</cbc:Amount>
            <cbc:BaseAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->monto_base_retencion}}</cbc:BaseAmount>
        </cac:AllowanceCharge>
    @endif
    @if($documento->facturacion->descuento_global>'0.00')
        <cac:AllowanceCharge>
            <cbc:ChargeIndicator>false</cbc:ChargeIndicator>
            <cbc:AllowanceChargeReasonCode>02</cbc:AllowanceChargeReasonCode>
            <cbc:MultiplierFactorNumeric>{{$documento->facturacion->porcentaje_descuento_global}}</cbc:MultiplierFactorNumeric>
            <cbc:Amount currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->descuento_global}}</cbc:Amount>
            <cbc:BaseAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->base_descuento_global}}</cbc:BaseAmount>
        </cac:AllowanceCharge>
    @endif
    @if($documento->codigo_tipo_factura == '0200')
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">0.00</cbc:TaxAmount>
            <cac:TaxSubtotal>
                <cbc:TaxableAmount
                        currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->total_gravadas}}</cbc:TaxableAmount>
                <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">0</cbc:TaxAmount>
                <cac:TaxCategory>
                    <cac:TaxScheme>
                        <cbc:ID>9995</cbc:ID>
                        <cbc:Name>EXP</cbc:Name>
                        <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                    </cac:TaxScheme>
                </cac:TaxCategory>
            </cac:TaxSubtotal>
        </cac:TaxTotal>
    @else
        <cac:TaxTotal>
            <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->total_impuestos}}</cbc:TaxAmount>
            @if($documento->facturacion->igv>'0.00')
                <cac:TaxSubtotal>
                    <cbc:TaxableAmount
                            currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->total_gravadas}}</cbc:TaxableAmount>
                    <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->igv}}</cbc:TaxAmount>
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
                    <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->facturacion->isc}}</cbc:TaxAmount>
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
                    <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">{{round($documento->facturacion->total_gratuitas * 0.18,2)}}</cbc:TaxAmount>
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
    @endif
    <cac:LegalMonetaryTotal>
        @if($documento->codigo_tipo_factura == '0200')
            <cbc:LineExtensionAmount
                    currencyID="{{ $documento->codigo_moneda }}">{{$documento->facturacion->total_gravadas}}</cbc:LineExtensionAmount>
           @else
            <cbc:LineExtensionAmount
                    currencyID="{{ $documento->codigo_moneda }}">{{$documento->facturacion->base_descuento_global - $documento->facturacion->descuento_global}}</cbc:LineExtensionAmount>
        @endif
            <cbc:TaxInclusiveAmount currencyID="{{ $documento->codigo_moneda }}">{{$documento->total_venta}}</cbc:TaxInclusiveAmount>
        @if ($documento->facturacion->total_descuentos>'0.00')
            <cbc:AllowanceTotalAmount
                    currencyID="{{ $documento->codigo_moneda }}">0</cbc:AllowanceTotalAmount>
        @endif
        @if ($documento->otros_cargos)
            <cbc:ChargeTotalAmount
                    currencyID="{{ $documento->codigo_moneda }}">{{$documento->otros_cargos}}</cbc:ChargeTotalAmount>
        @endif
        @if ($documento->anticipos)
            <cbc:PrepaidAmount
                    currencyID="{{ $documento->codigo_moneda }}">{{$documento->anticipos}}</cbc:PrepaidAmount>
        @endif
        <cbc:PayableAmount currencyID="{{$documento->codigo_moneda}}">{{$documento->total_venta}}</cbc:PayableAmount>
    </cac:LegalMonetaryTotal>
    @foreach($items as $item)
        <cac:InvoiceLine>
            <cbc:ID>{{$item->num_item}}</cbc:ID>
            <cbc:InvoicedQuantity unitCode="{{$item->codigo_medida}}">{{$item->cantidad}}</cbc:InvoicedQuantity>
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
                    <cbc:BaseAmount currencyID="{{$documento->codigo_moneda}}">{{$item->base_descuento}}</cbc:BaseAmount>
                </cac:AllowanceCharge>
            @endif
            @if($documento->codigo_tipo_factura == '0200')
                <cac:TaxTotal>
                    <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">0.00</cbc:TaxAmount>
                    <cac:TaxSubtotal>
                        <cbc:TaxableAmount
                                currencyID="{{$documento->codigo_moneda}}">{{$item->detalle->subtotal}}</cbc:TaxableAmount>
                        <cbc:TaxAmount currencyID="{{$documento->codigo_moneda}}">0.00</cbc:TaxAmount>
                        <cac:TaxCategory>
                            <cbc:Percent>0</cbc:Percent>
                            <cbc:TaxExemptionReasonCode>40</cbc:TaxExemptionReasonCode>
                            <cac:TaxScheme>
                                <cbc:ID>9995</cbc:ID>
                                <cbc:Name>EXP</cbc:Name>
                                <cbc:TaxTypeCode>FRE</cbc:TaxTypeCode>
                            </cac:TaxScheme>
                        </cac:TaxCategory>
                    </cac:TaxSubtotal>
                </cac:TaxTotal>
            @else
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
            @endif
            <cac:Item>
                <cbc:Description><![CDATA[{{preg_replace('/\s+/', ' ', preg_replace("/[\r\n|\n|\r]+/", " ", strip_tags($item->descripcion)))}}]]></cbc:Description>
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
                            currencyID="{{$documento->codigo_moneda}}">0.00</cbc:PriceAmount>
                </cac:Price>
            @else
                @if($documento->codigo_tipo_factura == '0200')
                    <cac:Price>
                        <cbc:PriceAmount
                                currencyID="{{$documento->codigo_moneda}}">{{$item->valor_referencial}}</cbc:PriceAmount>
                    </cac:Price>
                @else
                    <cac:Price>
                        <cbc:PriceAmount
                                currencyID="{{$documento->codigo_moneda}}">{{$item->valor_venta_bruto_unitario}}</cbc:PriceAmount>
                    </cac:Price>
                @endif
            @endif
        </cac:InvoiceLine>
    @endforeach
</Invoice>