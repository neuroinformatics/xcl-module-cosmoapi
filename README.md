# CosmoAPI
![php](https://img.shields.io/badge/PHP-5.0-blue.svg)
![xoops](https://img.shields.io/badge/XOOPS-module-green.svg)
![license](https://img.shields.io/badge/license-GPL2.0-blue.svg)

Web API implementation for CosmoDB

## Install
- upload files to your XOOPS Cube Legacy installation
- install `cosmoapi` module from XOOPS administration page

## Usage
## Functions
- login
- get data list
- keyword search
- get detail information
- get attached file
- update metadata
- upload file

## login
- request
```
[XOOPS_URL]/modules/cosmoapi/index.php/login?uname=[USERNAME]&pass=[PASSWORD]
```
- result
```xml
<result>
<login>success</login>
</result>
```

## get data list
- request
```
[XOOPS_URL]/modules/cosmoapi/index.php/list/[MODULE NAME]
```

- return
```xml
<cosmodb name="newdb5">
<request>
<criteria name="keyword" />
</request>
<results>
<data data_id="219">001213_3_kj</data>
<data data_id="221">000802_1_hi</data>
<data data_id="224">010424_1_sw</data>
<data data_id="234">981219_3_kg</data>
<data data_id="236">000703_1_hi</data>
<data data_id="238">000714_2_hi</data>
...
<data data_id="568">091122_1_sn</data>
</results>
</cosmodb>
```

## keyword search
- request
```
[XOOPS_URL]/modules/cosmoapi/index.php/serarch/[MODULE NAME]?keyword=[SEARCH KEYWORD]
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
[XOOPS_URL]/modules/cosmoapi/index.php/get/[MODULE NAME]/[DATA ID]
```

- result
```xml
<cosmodb name="newdb5">
<data data_id="570" url="[XOOPS_URL]/modules/newdb5/detail.php?id=570">
<label>010910_1_sh</label>
<author user_id="207">iwatsuki</author>
<date>2015-02-13</date>
<views>105</views>
<metadata>
<component comp_id="7" name="SeqNo"/>
<component comp_id="8" name="Title"/>
</metadata>
<keywords>
<keyword keyword_id="38" path="2" sort="0">Neuron Type/AL-PN</keyword>
<keyword keyword_id="8" path="1" sort="0">Arborization Area/AL</keyword>
<keyword keyword_id="16" path="1" sort="0">Arborization Area/AL/MGC</keyword>
<keyword keyword_id="22" path="1" sort="0">Arborization Area/LPC</keyword>
<keyword keyword_id="23" path="1" sort="0">Arborization Area/MB</keyword>
<keyword keyword_id="26" path="1" sort="0">Arborization Area/MB/Calyx</keyword>
<keyword keyword_id="68" path="3" sort="0">Cell Body Position/AL-MC</keyword>
<keyword keyword_id="86" path="5" sort="0">Physiology/Olfactory</keyword>
<keyword keyword_id="87" path="5" sort="0">Physiology/Olfactory/Bombykol</keyword>
<keyword keyword_id="88" path="5" sort="0">Physiology/Olfactory/Bombykal</keyword>
<keyword keyword_id="92" path="5" sort="0">Physiology/Olfactory/Mixture</keyword>
<keyword keyword_id="78" path="4" sort="0">Dye/Lucifer Yellow</keyword>
<keyword keyword_id="126" path="7" sort="0">Other/One Set Data</keyword>
</keywords>
<thumbnails>
<thumbnail>
<url>[XOOPS_URL]/modules/newdb5/extract/570/thumbnail/mor/010910_1f4z_sh.jpg</url>
<caption>Shibamoto Some rights reserved (CC:BY-NC-SA).</caption>
</thumbnail>
</thumbnails>
<items>
<item item_id="1821" type="dir" path="">Morphology</item>
<item item_id="1823" type="dir" path="Morphology">Export</item>
<item item_id="1824" type="dir" path="Morphology">LSM</item>
<item item_id="1828" type="file" path="Morphology/LSM">IMAGES.zip</item>
<item item_id="1829" type="file" path="Morphology/Export">010910_1f4z_sh.psd</item>
</items>
<comments>
<topic topic_id="571" com_id="571" type="auth">
<comment com_id="571" pcom_id="0" reg_date="1423802516" reg_user="207" subject="Auther's Comment">BoND ID: 169 Author: Shibamoto</comment>
</topic>
</comments>
<links>
<link link_id="1" type="external" uid="207" name="Cockroach Optic Lobe" href="https://invbrain.neuroinf.jp/modules/htmldocs/IVBPF/Cockroach/Cockroach_optic_lobe.html" note="Comparative table"/>
</links>
</data>
</cosmodb>
```

## get attached file
- request
```
[XOOPS_URL]/modules/cosmoapi/index.php/download/[MODULE NAME]/[FILE ID]
```

- result
file.

## update metadata
- request
```
[XOOPS_URL]/modules/cosmoapi/index.php/update/[MODULE NAME]/[DATA ID]
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

## upload file
- request
```
[XOOPS_URL]/modules/cosmoapi/index.php/upload/[MODULE NAME]/[DATA ID]
```
with `path=[FILE PATH]` and `file=[FILE(multipart/form-data)]` by POST method.

- return
```xml
<cosmodb name="newdb5">
<data data_id="570">
<result>
<upload>success</upload>
</result>
</data>
</cosmodb>
```
