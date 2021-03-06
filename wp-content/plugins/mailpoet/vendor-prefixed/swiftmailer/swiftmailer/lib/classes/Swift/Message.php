<?php
namespace MailPoetVendor;
if (!defined('ABSPATH')) exit;
class Swift_Message extends Swift_Mime_SimpleMessage
{
 private $headerSigners = [];
 private $bodySigners = [];
 private $savedMessage = [];
 public function __construct($subject = null, $body = null, $contentType = null, $charset = null)
 {
 \call_user_func_array([$this, 'MailPoetVendor\\Swift_Mime_SimpleMessage::__construct'], Swift_DependencyContainer::getInstance()->createDependenciesFor('mime.message'));
 if (!isset($charset)) {
 $charset = Swift_DependencyContainer::getInstance()->lookup('properties.charset');
 }
 $this->setSubject($subject);
 $this->setBody($body);
 $this->setCharset($charset);
 if ($contentType) {
 $this->setContentType($contentType);
 }
 }
 public function addPart($body, $contentType = null, $charset = null)
 {
 return $this->attach((new Swift_MimePart($body, $contentType, $charset))->setEncoder($this->getEncoder()));
 }
 public function attachSigner(Swift_Signer $signer)
 {
 if ($signer instanceof Swift_Signers_HeaderSigner) {
 $this->headerSigners[] = $signer;
 } elseif ($signer instanceof Swift_Signers_BodySigner) {
 $this->bodySigners[] = $signer;
 }
 return $this;
 }
 public function detachSigner(Swift_Signer $signer)
 {
 if ($signer instanceof Swift_Signers_HeaderSigner) {
 foreach ($this->headerSigners as $k => $headerSigner) {
 if ($headerSigner === $signer) {
 unset($this->headerSigners[$k]);
 return $this;
 }
 }
 } elseif ($signer instanceof Swift_Signers_BodySigner) {
 foreach ($this->bodySigners as $k => $bodySigner) {
 if ($bodySigner === $signer) {
 unset($this->bodySigners[$k]);
 return $this;
 }
 }
 }
 return $this;
 }
 public function clearSigners()
 {
 $this->headerSigners = [];
 $this->bodySigners = [];
 return $this;
 }
 public function toString()
 {
 if (empty($this->headerSigners) && empty($this->bodySigners)) {
 return parent::toString();
 }
 $this->saveMessage();
 $this->doSign();
 $string = parent::toString();
 $this->restoreMessage();
 return $string;
 }
 public function toByteStream(Swift_InputByteStream $is)
 {
 if (empty($this->headerSigners) && empty($this->bodySigners)) {
 parent::toByteStream($is);
 return;
 }
 $this->saveMessage();
 $this->doSign();
 parent::toByteStream($is);
 $this->restoreMessage();
 }
 public function __wakeup()
 {
 Swift_DependencyContainer::getInstance()->createDependenciesFor('mime.message');
 }
 protected function doSign()
 {
 foreach ($this->bodySigners as $signer) {
 $altered = $signer->getAlteredHeaders();
 $this->saveHeaders($altered);
 $signer->signMessage($this);
 }
 foreach ($this->headerSigners as $signer) {
 $altered = $signer->getAlteredHeaders();
 $this->saveHeaders($altered);
 $signer->reset();
 $signer->setHeaders($this->getHeaders());
 $signer->startBody();
 $this->bodyToByteStream($signer);
 $signer->endBody();
 $signer->addSignature($this->getHeaders());
 }
 }
 protected function saveMessage()
 {
 $this->savedMessage = ['headers' => []];
 $this->savedMessage['body'] = $this->getBody();
 $this->savedMessage['children'] = $this->getChildren();
 if (\count($this->savedMessage['children']) > 0 && '' != $this->getBody()) {
 $this->setChildren(\array_merge([$this->becomeMimePart()], $this->savedMessage['children']));
 $this->setBody('');
 }
 }
 protected function saveHeaders(array $altered)
 {
 foreach ($altered as $head) {
 $lc = \strtolower($head ?? '');
 if (!isset($this->savedMessage['headers'][$lc])) {
 $this->savedMessage['headers'][$lc] = $this->getHeaders()->getAll($head);
 }
 }
 }
 protected function restoreHeaders()
 {
 foreach ($this->savedMessage['headers'] as $name => $savedValue) {
 $headers = $this->getHeaders()->getAll($name);
 foreach ($headers as $key => $value) {
 if (!isset($savedValue[$key])) {
 $this->getHeaders()->remove($name, $key);
 }
 }
 }
 }
 protected function restoreMessage()
 {
 $this->setBody($this->savedMessage['body']);
 $this->setChildren($this->savedMessage['children']);
 $this->restoreHeaders();
 $this->savedMessage = [];
 }
 public function __clone()
 {
 parent::__clone();
 foreach ($this->bodySigners as $key => $bodySigner) {
 $this->bodySigners[$key] = clone $bodySigner;
 }
 foreach ($this->headerSigners as $key => $headerSigner) {
 $this->headerSigners[$key] = clone $headerSigner;
 }
 }
}
