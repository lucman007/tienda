<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>
<DespatchAdvice xmlns="urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2"
                xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
                xmlns:sac="urn:sunat:names:specification:ubl:peru:schema:xsd:SunatAggregateComponents-1"
                xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent>
            </ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>2.0</cbc:CustomizationID>
    <cbc:ID>{{$documento->serie_correlativo}}</cbc:ID>
    <cbc:IssueDate>{{$documento->fecha_emision}}</cbc:IssueDate>
    <cbc:IssueTime>{{$documento->hora_emision}}</cbc:IssueTime>
    <cbc:DespatchAdviceTypeCode listAgencyName="PE:SUNAT" listName="Tipo de Documento"
                                listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo01">{{$documento->tipo_guia}}</cbc:DespatchAdviceTypeCode>
    @if($documento->observacion_guia)
        <cbc:Note><![CDATA[{{$documento->observacion_guia}}]]></cbc:Note>
    @endif
    @if(!($documento->doc_relacionado == -1 || $documento->doc_relacionado == -30))
    <cac:AdditionalDocumentReference>
        <cbc:ID>{{ $documento->num_doc_relacionado }}</cbc:ID>
        <cbc:DocumentTypeCode listAgencyName="PE:SUNAT" listName="Documento relacionado al transporte"
                              listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo61">{{ $documento->doc_relacionado }}</cbc:DocumentTypeCode>
        <cbc:DocumentType>NumeroOtros</cbc:DocumentType>
        <cac:IssuerParty>
            <cac:PartyIdentification>
                <cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT"
                        schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{$emisor->ruc}}</cbc:ID>
            </cac:PartyIdentification>
        </cac:IssuerParty>
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
    <cac:DespatchSupplierParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT"
                        schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{$emisor->ruc}}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{$emisor->razon_social}}]]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:DespatchSupplierParty>
    <cac:DeliveryCustomerParty>
        <cac:Party>
            <cac:PartyIdentification>
                <cbc:ID schemeID="{{ $usuario->tipo_documento }}" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT"
                        schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{ $usuario->num_documento }}</cbc:ID>
            </cac:PartyIdentification>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{ $usuario->razon_social }}]]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:DeliveryCustomerParty>
    @if($documento->num_documento_tercero)
        <cac:SellerSupplierParty>
            <cbc:CustomerAssignedAccountID
                    schemeID="{{ $documento->tipo_documento_tercero }}">{{ $documento->num_documento_tercero }}</cbc:CustomerAssignedAccountID>
            <cac:Party>
                <cac:PartyLegalEntity>
                    <cbc:RegistrationName><![CDATA[{{ $documento->razon_social_tercero }}]]></cbc:RegistrationName>
                </cac:PartyLegalEntity>
            </cac:Party>
        </cac:SellerSupplierParty>
    @endif
    <cac:Shipment>
        <cbc:ID>1</cbc:ID>
        <cbc:HandlingCode listAgencyName="PE:SUNAT" listName="Motivo de traslado"
                          listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo20">{{ $documento->codigo_traslado }}</cbc:HandlingCode>
        <cbc:HandlingInstructions>{{ $documento->motivo_traslado }}</cbc:HandlingInstructions>
        <cbc:GrossWeightMeasure
                unitCode="{{ $documento->unidad_medida_peso_bruto }}">{{$documento->peso_bruto}}</cbc:GrossWeightMeasure>
        @if($documento->codigo_traslado=='08' || $documento->codigo_traslado=='09')
            <cbc:TotalTransportHandlingUnitQuantity>{{$documento->cantidad_bultos}}</cbc:TotalTransportHandlingUnitQuantity>
        @endif
        @if($documento->categoria_vehiculo == 'M1_L')
            <cbc:SpecialInstructions>SUNAT_Envio_IndicadorTrasladoVehiculoM1L</cbc:SpecialInstructions>
        @endif
        <cac:ShipmentStage>
            <cbc:TransportModeCode listAgencyName="PE:SUNAT" listName="Modalidad de traslado"
                                   listURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo18">{{$documento->codigo_transporte}}</cbc:TransportModeCode>
            <cac:TransitPeriod>
                <cbc:StartDate>{{ $documento->fecha_traslado }}</cbc:StartDate>
            </cac:TransitPeriod>
            @if($documento->categoria_vehiculo != 'M1_L')
                @if($documento->codigo_transporte == '01')
                    <cac:CarrierParty>
                        <cac:PartyIdentification>
                            <cbc:ID schemeID="6" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT"
                                    schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{ $documento->num_doc_transportista }}</cbc:ID>
                        </cac:PartyIdentification>
                        <cac:PartyLegalEntity>
                            <cbc:RegistrationName><![CDATA[{{ $documento->razon_social_transportista }}]]></cbc:RegistrationName>
                            <cbc:CompanyID>{{ $documento->registro_mtc }}</cbc:CompanyID>
                        </cac:PartyLegalEntity>
                    </cac:CarrierParty>
                @elseif($documento->codigo_transporte == '02')
                    <cac:DriverPerson>
                        <cbc:ID schemeID="1" schemeName="Documento de Identidad" schemeAgencyName="PE:SUNAT"
                                schemeURI="urn:pe:gob:sunat:cpe:see:gem:catalogos:catalogo06">{{ $documento->dni_conductor }}</cbc:ID>
                        <cbc:FirstName>{{ $documento->nombre_conductor }}</cbc:FirstName>
                        <cbc:FamilyName>{{ $documento->apellido_conductor }}</cbc:FamilyName>
                        <cbc:JobTitle>Principal</cbc:JobTitle>
                        <cac:IdentityDocumentReference>
                            <cbc:ID>{{ $documento->licencia_conductor }}</cbc:ID>
                        </cac:IdentityDocumentReference>
                    </cac:DriverPerson>
                @endif
            @endif
        </cac:ShipmentStage>
        <cac:Delivery>
            <cac:DeliveryAddress>
                <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">{{ $documento->ubigeo_direccion_llegada }}</cbc:ID>
                @if($documento->codigo_traslado == '04')
                <cbc:AddressTypeCode listID="{{$emisor->ruc}}" listAgencyName="PE:SUNAT" listName="Establecimientos anexos">{{$emisor->codigo_establecimiento}}</cbc:AddressTypeCode>
                @endif
                <cac:AddressLine>
                    <cbc:Line>{{ $documento->direccion_llegada }}</cbc:Line>
                </cac:AddressLine>
            </cac:DeliveryAddress>
            <cac:Despatch>
                <cac:DespatchAddress>
                    <cbc:ID schemeName="Ubigeos" schemeAgencyName="PE:INEI">{{ $emisor->ubigeo }}</cbc:ID>
                    @if($documento->codigo_traslado == '04')
                        <cbc:AddressTypeCode listID="{{$emisor->ruc}}" listAgencyName="PE:SUNAT" listName="Establecimientos anexos">{{ $emisor->codigo_establecimiento == '0001' ? '0000' : '0001' }}</cbc:AddressTypeCode>
                    @endif
                    <cac:AddressLine>
                        <cbc:Line>{{ $emisor->direccion_resumida }}</cbc:Line>
                    </cac:AddressLine>
                </cac:DespatchAddress>
            </cac:Despatch>
        </cac:Delivery>
        @if($documento->codigo_transporte == '02')
        <cac:TransportHandlingUnit>
            <cac:TransportEquipment>
                <cbc:ID>{{ $documento->placa_vehiculo }}</cbc:ID>
            </cac:TransportEquipment>
        </cac:TransportHandlingUnit>
        @endif
        @if($documento->num_contenedor)
            <cac:TransportHandlingUnit>
                <cbc:ID>{{$documento->num_contenedor}}</cbc:ID>
            </cac:TransportHandlingUnit>
        @endif
        <cac:OriginAddress>
            <cbc:ID>{{ $documento->direccion_partida_ubigeo }}</cbc:ID>
            <cbc:StreetName>{{ $documento->direccion_partida }}</cbc:StreetName>
        </cac:OriginAddress>
        @if($documento->codigo_puerto)
            <cac:FirstArrivalPortLocation>
                <cbc:ID>{{ $documento->codigo_puerto }}</cbc:ID>
            </cac:FirstArrivalPortLocation>
        @endif
    </cac:Shipment>
    @foreach($items as $item)
        <cac:DespatchLine>
            <cbc:ID>{{$item->num_item}}</cbc:ID>
            <cbc:DeliveredQuantity unitCode="{{ $item->codigo_medida }}" unitCodeListID="UN/ECE rec 20"
                                   unitCodeListAgencyName="United Nations Economic Commission for Europe">{{ $item->cantidad }}</cbc:DeliveredQuantity>
            <cac:OrderLineReference>
                <cbc:LineID>{{$item->num_item}}</cbc:LineID>
            </cac:OrderLineReference>
            <cac:Item>
                <cbc:Description><![CDATA[{{ preg_replace("/[\r\n|\n|\r]+/", " ",strip_tags($item->descripcion)) }}]]></cbc:Description>
                <cac:SellersItemIdentification>
                    <cbc:ID>{{ $item->codigo }}</cbc:ID>
                </cac:SellersItemIdentification>
            </cac:Item>
        </cac:DespatchLine>
    @endforeach
</DespatchAdvice>