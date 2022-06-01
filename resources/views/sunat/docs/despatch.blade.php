<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>
<DespatchAdvice xmlns="urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2"
                xmlns:ds="http://www.w3.org/2000/09/xmldsig#"
                xmlns:cac="urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2"
                xmlns:cbc="urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2"
                xmlns:ext="urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2">
    <ext:UBLExtensions>
        <ext:UBLExtension>
            <ext:ExtensionContent>
            </ext:ExtensionContent>
        </ext:UBLExtension>
    </ext:UBLExtensions>
    <cbc:UBLVersionID>2.1</cbc:UBLVersionID>
    <cbc:CustomizationID>1.0</cbc:CustomizationID>
    <cbc:ID>{{$documento->serie_correlativo}}</cbc:ID>
    <cbc:IssueDate>{{$documento->fecha_emision}}</cbc:IssueDate>
    <cbc:IssueTime>{{$documento->hora_emision}}</cbc:IssueTime>
    <cbc:DespatchAdviceTypeCode>{{$documento->tipo_guia}}</cbc:DespatchAdviceTypeCode>
    @if($documento->observacion_guia)
        <cbc:Note><![CDATA[{{$documento->observacion_guia}}]]></cbc:Note>
    @endif
    @if($documento->num_guia_baja)
        <cac:OrderReference>
            <cbc:ID>{{ $documento->num_guia_baja }}</cbc:ID>
            <cbc:OrderTypeCode name="GUÍA DE REMISIÓN REMITENTE">{{$documento->guia_baja_tipo}}</cbc:OrderTypeCode>
        </cac:OrderReference>
    @endif
    @if($documento->doc_relacionado != -1)
    <cac:AdditionalDocumentReference>
        <cbc:ID>{{ $documento->num_doc_relacionado }}</cbc:ID>
        <cbc:DocumentTypeCode>{{ $documento->doc_relacionado }}</cbc:DocumentTypeCode>
    </cac:AdditionalDocumentReference>
    @endif
    <cac:DespatchSupplierParty>
        <cbc:CustomerAssignedAccountID schemeID="{{$emisor->tipo_documento}}">{{$emisor->ruc}}</cbc:CustomerAssignedAccountID>
        <cac:Party>
            <cac:PartyLegalEntity>
                <cbc:RegistrationName><![CDATA[{{$emisor->razon_social}}]]></cbc:RegistrationName>
            </cac:PartyLegalEntity>
        </cac:Party>
    </cac:DespatchSupplierParty>
    <cac:DeliveryCustomerParty>
        <cbc:CustomerAssignedAccountID
                schemeID="{{ $usuario->tipo_documento }}">{{ $usuario->num_documento }}</cbc:CustomerAssignedAccountID>
        <cac:Party>
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
        <cbc:HandlingCode>{{ $documento->codigo_traslado }}</cbc:HandlingCode>
        <cbc:Information>{{ $documento->motivo_traslado }}</cbc:Information>
        <cbc:GrossWeightMeasure
                unitCode="{{ $documento->unidad_medida_peso_bruto }}">{{$documento->peso_bruto}}
        </cbc:GrossWeightMeasure>
        @if($documento->codigo_traslado=='08' || $documento->codigo_traslado=='09')
            <cbc:TotalTransportHandlingUnitQuantity>{{$documento->cantidad_bultos}}</cbc:TotalTransportHandlingUnitQuantity>
        @endif
        <cbc:SplitConsignmentIndicator>{{ $documento->indicador_transbordo_programado }}</cbc:SplitConsignmentIndicator>
        <cac:ShipmentStage>
            <cbc:TransportModeCode>{{$documento->codigo_transporte}}</cbc:TransportModeCode>
            <cac:TransitPeriod>
                <cbc:StartDate>{{ $documento->fecha_traslado }}</cbc:StartDate>
            </cac:TransitPeriod>
            @if($documento->codigo_transporte == '01')
                <cac:CarrierParty>
                    <cac:PartyIdentification>
                        <cbc:ID schemeID="{{ $documento->tipo_doc_transportista }}">{{ $documento->num_doc_transportista }}</cbc:ID>
                    </cac:PartyIdentification>
                    <cac:PartyName>
                        <cbc:Name><![CDATA[{{ $documento->razon_social_transportista }}]]></cbc:Name>
                    </cac:PartyName>
                </cac:CarrierParty>
            @elseif($documento->codigo_transporte == '02')
                <cac:TransportMeans>
                    <cac:RoadTransport>
                        <cbc:LicensePlateID>{{ $documento->placa_vehiculo }}</cbc:LicensePlateID>
                    </cac:RoadTransport>
                </cac:TransportMeans>
                <cac:DriverPerson>
                    <cbc:ID schemeID="1">{{ $documento->dni_conductor }}</cbc:ID>
                </cac:DriverPerson>
            @endif
        </cac:ShipmentStage>
        <cac:Delivery>
            <cac:DeliveryAddress>
                <cbc:ID>{{ $documento->ubigeo_direccion_llegada }}</cbc:ID>
                <cbc:StreetName>{{ $documento->direccion_llegada }}</cbc:StreetName>
            </cac:DeliveryAddress>
        </cac:Delivery>
        @if($documento->num_contenedor)
            <cac:TransportHandlingUnit>
                <cbc:ID>{{$documento->num_contenedor}}</cbc:ID>
            </cac:TransportHandlingUnit>
        @endif
        <cac:OriginAddress>
            <cbc:ID>{{ $emisor->ubigeo }}</cbc:ID>
            <cbc:StreetName>{{ $emisor->direccion_resumida }}</cbc:StreetName>
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
            <cbc:DeliveredQuantity unitCode="{{ $item->codigo_medida }}">{{ $item->cantidad }}</cbc:DeliveredQuantity>
            <cac:OrderLineReference>
                <cbc:LineID>{{$item->num_item}}</cbc:LineID>
            </cac:OrderLineReference>
            <cac:Item>
                <cbc:Name><![CDATA[{{ $item->descripcion }}]]></cbc:Name>
                <cac:SellersItemIdentification>
                    <cbc:ID>{{ $item->codigo }}</cbc:ID>
                </cac:SellersItemIdentification>
            </cac:Item>
        </cac:DespatchLine>
    @endforeach
</DespatchAdvice>