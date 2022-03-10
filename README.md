UtitilyBundle
------------------

## DTO

### Attributes

Uniwersalny atrybut zasobu, przeznaczony w głównej mierze do obsługi DTO w ramach bundli LSB. Dopuszczalny zakres użycia:
* klasa
  * encja
  * kontroler
* metoda (akcja)

Zasada użycia (w nawiasie priorytet danych 1 - najniższy, 3 najwyższy):
* na poziomie encji, określamy klasy obiektów DTO dla danej encji (1)
* na poziomie kontrolera wskazujemy klasę managera, dedykowanego do obsługi danego kontrolera (2)
* na poziomie metody wskazujemy typ deserializacji jaki chcemy użyć (3)




    #[Resource(
    inputCreateDTOClass: CreateProductInputDTO::class,
    outputDTOClass: ProductOutputDTO::class)]



## Serializer

### ExistingObjectConstructor

Intended for JMSSerializerBundle

In order to use, overwrite the definition of service:
* `jms_serializer.deserialization_graph_navigator_factory`:


    jms_serializer.deserialization_graph_navigator_factory:
        class: JMS\Serializer\GraphNavigator\Factory\DeserializationGraphNavigatorFactory
        arguments:
            - '@jms_serializer.metadata_factory'
            - '@jms_serializer.handler_registry'
            - '@lsb_utility.jms.serializer.existing_object_constructor'
            - '@jms_serializer.accessor_strategy'
            - '@jms_serializer.event_dispatcher'
            - null