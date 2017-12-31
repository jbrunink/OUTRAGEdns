<?php


namespace OUTRAGEdns\Domain;

use \Net_DNS2_Exception as DnsException;
use \Net_DNS2_Packet_Response as DnsPacketResponse;
use \Net_DNS2_Resolver as DnsResolver;
use \OUTRAGEdns\Record\Content as RecordContent;


class DomainTest
{
	/**
	 *	Perform tests to DNS servers (via the DNS protocol)
	 */
	public function testWithDNS(Content $domain, array $records, array $nameservers)
	{
		foreach($nameservers as $key => $list)
		{
			$resolver = new DnsResolver([
				"nameservers" => $list,
				"use_tcp" => true,
			]);
			
			foreach($records as $record)
			{
				if(!isset($results[$record->id]))
					$results[$record->id] = [];
				
				try
				{
					$name = $domain->name;
					
					if(strlen($record->name) > 0)
					{
						if(stristr($record->name, "*") !== false)
							$name = str_replace("*", "outragedns-wildcard-test-".uniqid(), $record->name);
						else
							$name = $record->name;
					}
					
					$results[$record->id][$key] = $this->parseDNSResult($name, $record, $resolver->query($name, $record->type));
				}
				catch(DnsException $exception)
				{
					$results[$record->id][$key] = null;
				}
			}
		}
		
		return $results;
	}
	
	
	/**
	 *	Parse the response from the resolver
	 */
	protected function parseDNSResult($requested_name, RecordContent $record, DnsPacketResponse $result)
	{
		foreach($result->answer as $answer)
		{
			$list = [
				$answer->name == $requested_name,
			];
			
			foreach($record->rdata as $rkey => $rvalue)
			{
				switch($record->type)
				{
					case "TXT":
						foreach($answer->text as $text)
							$list[] = strcmp($rvalue, $text) === 0;
					break;
					
					default:
						$akey = strtolower($rkey);
						
						if(isset($answer->{$akey}))
							$list[] = rtrim($answer->{$akey}, ".") == rtrim($rvalue, ".");
					break;
				}
			}
			
			if(count($list) > 0)
			{
				$list = array_unique($list);
				
				if(count(array_filter($list)) === count($list))
					return true;
			}
		}
		
		return false;
	}
}