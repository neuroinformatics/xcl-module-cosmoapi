# xcl-module-cosmoapi
![php](https://img.shields.io/badge/PHP-5.0-blue.svg)
![xoops](https://img.shields.io/badge/XOOPS-module-green.svg)
![license](https://img.shields.io/badge/license-GPL2.0-blue.svg)

Web API implementation for CosmoDB

## Install

## Usage
## Functions
- login
- keyword search
- get detail information
- get attached file
- update metadata

## login
- request
```
http://XOOPS_URL/modules/cosmoapi/index.php/login?uname=[USERNAME]&pass=[PASSWORD] 
```
- result
```xml
<result>
<login>success</login>
</result>
```

## keyword search
- request
```
http://XOOPS_URL/modules/cosmoapi/index.php/serarch/[MODULE NAME]?keyword=[SEARCH KEYWORD]
```

- result
```xml
<cosmodb name="newdb5">
<request>
<criteria name="keyword">SWC</criteria>
</request>
<results>
<data data_id="219">001213_3_kj</data>
<data data_id="221">000802_1_hi</data>
...
<data data_id="568">091122_1_sn</data>
</results>
</cosmodb>
```

## get detail information
- request
```
http://XOOPS_URL/modules/cosmoapi/index.php/get/[MODULE NAME]/[DATA ID]
```

- result
```xml
<cosmodb name="newdb5">
<data data_id="570">
<label>010910_1_sh</label>
<author user_id="207">iwatsuki</author>
<date>2015-02-13</date>
<views>74</views>
<metadata>
<component comp_id="7" name="SeqNo"/>
<component comp_id="8" name="Title"/>
<component comp_id="9" name="TotalLength">10</component>
</metadata>
<keywords>
<keyword keyword_id="38">AL-PN</keyword>
<keyword keyword_id="8">AL</keyword>
<keyword keyword_id="16">AL/MGC</keyword>
<keyword keyword_id="22">LPC</keyword>
<keyword keyword_id="23">MB</keyword>
<keyword keyword_id="26">MB/Calyx</keyword>
<keyword keyword_id="68">AL-MC</keyword>
<keyword keyword_id="86">Olfactory</keyword>
<keyword keyword_id="87">Olfactory/Bombykol</keyword>
<keyword keyword_id="88">Olfactory/Bombykal</keyword>
<keyword keyword_id="92">Olfactory/Mixture</keyword>
<keyword keyword_id="78">Lucifer Yellow</keyword>
<keyword keyword_id="126">One Set Data</keyword>
</keywords>
<items>
<item item_id="1821" type="dir" path="">Morphology</item>
<item item_id="1823" type="dir" path="Morphology">Export</item>
<item item_id="1824" type="dir" path="Morphology">LSM</item>
<item item_id="1828" type="file" path="Morphology/LSM">IMAGES.zip</item>
<item item_id="1829" type="file" path="Morphology/Export">010910_1f4z_sh.psd</item>
</items>
</data>
</cosmodb>
```

## get attached file
- request
```
http://XOOPS_URL/modules/cosmoapi/index.php/download/[MODULE NAME]/[FILE ID]
```

- result
file.

## update metadata
- request
```
http://XOOPS_URL/modules/cosmoapi/index.php/update/[MODULE NAME]/[DATA ID]
```
with `key=[KEY]` and `value=[VALUE]` by POST method.

- return
```xml
<cosmodb name="newdb5">
<data data_id="570">
<result>
<update>success</update>
</result>
</data>
</cosmodb>
```
