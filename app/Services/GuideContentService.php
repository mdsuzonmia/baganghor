<?php
namespace App\Services;
class GuideContentService {
 public function clean(string $html): string {
  $doc=new \DOMDocument(); $previous=libxml_use_internal_errors(true); $doc->loadHTML('<?xml encoding="utf-8"?><div id="guide-root">'.$html.'</div>',LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);libxml_clear_errors();libxml_use_internal_errors($previous);
  $root=$doc->getElementById('guide-root');if(!$root)return '';
  $allowed=['p','br','h2','h3','h4','strong','b','em','i','u','ul','ol','li','blockquote','a'];
  $walk=function($node)use(&$walk,$allowed){foreach(iterator_to_array($node->childNodes) as $child){if($child->nodeType===XML_COMMENT_NODE){$node->removeChild($child);continue;}if($child->nodeType!==XML_ELEMENT_NODE)continue;$tag=strtolower($child->nodeName);if(!in_array($tag,$allowed,true)){if(in_array($tag,['script','style','iframe','object','embed','svg','math','form'],true)){$node->removeChild($child);continue;}$walk($child);while($child->firstChild)$node->insertBefore($child->firstChild,$child);$node->removeChild($child);continue;}foreach(iterator_to_array($child->attributes) as $attr){$child->removeAttributeNode($attr);if($tag==='a'&&$attr->name==='href'&&preg_match('~^(https?://|/|#)~i',trim($attr->value)))$child->setAttribute('href',trim($attr->value));}if($tag==='a')$child->setAttribute('rel','noopener noreferrer');$walk($child);}};$walk($root);
  $result='';foreach($root->childNodes as $child)$result.=$doc->saveHTML($child);return $result;
 }
 public function outline(string $html): array { $toc=[];$i=0;$html=preg_replace_callback('~<h([23])>(.*?)</h\1>~isu',function($m)use(&$toc,&$i){$id='section-'.(++$i);$toc[]=['id'=>$id,'level'=>(int)$m[1],'title'=>trim(strip_tags($m[2]))];return '<h'.$m[1].' id="'.$id.'">'.$m[2].'</h'.$m[1].'>';},$html);return [$html,$toc]; }
}
