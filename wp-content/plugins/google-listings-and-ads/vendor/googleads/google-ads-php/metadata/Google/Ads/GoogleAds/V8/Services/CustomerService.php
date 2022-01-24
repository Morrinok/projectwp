<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v8/services/customer_service.proto

namespace GPBMetadata\Google\Ads\GoogleAds\V8\Services;

class CustomerService
{
    public static $is_initialized = false;

    public static function initOnce() {
        $pool = \Google\Protobuf\Internal\DescriptorPool::getGeneratedPool();
        if (static::$is_initialized == true) {
          return;
        }
        \GPBMetadata\Google\Api\Http::initOnce();
        \GPBMetadata\Google\Api\Annotations::initOnce();
        \GPBMetadata\Google\Api\FieldBehavior::initOnce();
        \GPBMetadata\Google\Api\Resource::initOnce();
        \GPBMetadata\Google\Protobuf\FieldMask::initOnce();
        \GPBMetadata\Google\Api\Client::initOnce();
        $pool->internalAddGeneratedFile(
            '
�
/google/ads/googleads/v8/enums/access_role.protogoogle.ads.googleads.v8.enums"t
AccessRoleEnum"b

AccessRole
UNSPECIFIED 
UNKNOWN	
ADMIN
STANDARD
	READ_ONLY

EMAIL_ONLYB�
!com.google.ads.googleads.v8.enumsBAccessRoleProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
Zgoogle/ads/googleads/v8/enums/customer_pay_per_conversion_eligibility_failure_reason.protogoogle.ads.googleads.v8.enums"�
4CustomerPayPerConversionEligibilityFailureReasonEnum"�
0CustomerPayPerConversionEligibilityFailureReason
UNSPECIFIED 
UNKNOWN
NOT_ENOUGH_CONVERSIONS
CONVERSION_LAG_TOO_HIGH#
HAS_CAMPAIGN_WITH_SHARED_BUDGET 
HAS_UPLOAD_CLICKS_CONVERSION 
AVERAGE_DAILY_SPEND_TOO_HIGH
ANALYSIS_NOT_COMPLETE	
OTHERB�
!com.google.ads.googleads.v8.enumsB5CustomerPayPerConversionEligibilityFailureReasonProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
9google/ads/googleads/v8/enums/response_content_type.protogoogle.ads.googleads.v8.enums"o
ResponseContentTypeEnum"T
ResponseContentType
UNSPECIFIED 
RESOURCE_NAME_ONLY
MUTABLE_RESOURCEB�
!com.google.ads.googleads.v8.enumsBResponseContentTypeProtoPZBgoogle.golang.org/genproto/googleapis/ads/googleads/v8/enums;enums�GAA�Google.Ads.GoogleAds.V8.Enums�Google\\Ads\\GoogleAds\\V8\\Enums�!Google::Ads::GoogleAds::V8::Enumsbproto3
�
0google/ads/googleads/v8/resources/customer.proto!google.ads.googleads.v8.resourcesgoogle/api/field_behavior.protogoogle/api/resource.protogoogle/api/annotations.proto"�	
Customer@
resource_name (	B)�A�A#
!googleads.googleapis.com/Customer
id (B�AH �
descriptive_name (	H�
currency_code (	B�AH�
	time_zone (	B�AH�"
tracking_url_template (	H�
final_url_suffix (	H�!
auto_tagging_enabled (H�$
has_partners_badge (B�AH�
manager (B�AH�
test_account (B�AH	�W
call_reporting_setting
 (27.google.ads.googleads.v8.resources.CallReportingSettingf
conversion_tracking_setting (2<.google.ads.googleads.v8.resources.ConversionTrackingSettingB�AW
remarketing_setting (25.google.ads.googleads.v8.resources.RemarketingSettingB�A�
.pay_per_conversion_eligibility_failure_reasons (2�.google.ads.googleads.v8.enums.CustomerPayPerConversionEligibilityFailureReasonEnum.CustomerPayPerConversionEligibilityFailureReasonB�A$
optimization_score (B�AH
�&
optimization_score_weight (B�A:?�A<
!googleads.googleapis.com/Customercustomers/{customer_id}B
_idB
_descriptive_nameB
_currency_codeB

_time_zoneB
_tracking_url_templateB
_final_url_suffixB
_auto_tagging_enabledB
_has_partners_badgeB

_managerB
_test_accountB
_optimization_score"�
CallReportingSetting#
call_reporting_enabled
 (H �.
!call_conversion_reporting_enabled (H�S
call_conversion_action (	B.�A+
)googleads.googleapis.com/ConversionActionH�B
_call_reporting_enabledB$
"_call_conversion_reporting_enabledB
_call_conversion_action"�
ConversionTrackingSetting(
conversion_tracking_id (B�AH �6
$cross_account_conversion_tracking_id (B�AH�B
_conversion_tracking_idB\'
%_cross_account_conversion_tracking_id"Y
RemarketingSetting(
google_global_site_tag (	B�AH �B
_google_global_site_tagB�
%com.google.ads.googleads.v8.resourcesBCustomerProtoPZJgoogle.golang.org/genproto/googleapis/ads/googleads/v8/resources;resources�GAA�!Google.Ads.GoogleAds.V8.Resources�!Google\\Ads\\GoogleAds\\V8\\Resources�%Google::Ads::GoogleAds::V8::Resourcesbproto3
�
7google/ads/googleads/v8/services/customer_service.proto google.ads.googleads.v8.services9google/ads/googleads/v8/enums/response_content_type.proto0google/ads/googleads/v8/resources/customer.protogoogle/api/annotations.protogoogle/api/client.protogoogle/api/field_behavior.protogoogle/api/resource.proto google/protobuf/field_mask.proto"V
GetCustomerRequest@
resource_name (	B)�A�A#
!googleads.googleapis.com/Customer"�
MutateCustomerRequest
customer_id (	B�AK
	operation (23.google.ads.googleads.v8.services.CustomerOperationB�A
validate_only (i
response_content_type (2J.google.ads.googleads.v8.enums.ResponseContentTypeEnum.ResponseContentType"�
CreateCustomerClientRequest
customer_id (	B�AI
customer_client (2+.google.ads.googleads.v8.resources.CustomerB�A
email_address (	H �M
access_role (28.google.ads.googleads.v8.enums.AccessRoleEnum.AccessRole
validate_only (B
_email_address"�
CustomerOperation;
update (2+.google.ads.googleads.v8.resources.Customer/
update_mask (2.google.protobuf.FieldMask"N
CreateCustomerClientResponse
resource_name (	
invitation_link (	"`
MutateCustomerResponseF
result (26.google.ads.googleads.v8.services.MutateCustomerResult"l
MutateCustomerResult
resource_name (	=
customer (2+.google.ads.googleads.v8.resources.Customer" 
ListAccessibleCustomersRequest"9
ListAccessibleCustomersResponse
resource_names (	2�
CustomerService�
GetCustomer4.google.ads.googleads.v8.services.GetCustomerRequest+.google.ads.googleads.v8.resources.Customer"7���!/v8/{resource_name=customers/*}�Aresource_name�
MutateCustomer7.google.ads.googleads.v8.services.MutateCustomerRequest8.google.ads.googleads.v8.services.MutateCustomerResponse"G���)"$/v8/customers/{customer_id=*}:mutate:*�Acustomer_id,operation�
ListAccessibleCustomers@.google.ads.googleads.v8.services.ListAccessibleCustomersRequestA.google.ads.googleads.v8.services.ListAccessibleCustomersResponse"-���\'%/v8/customers:listAccessibleCustomers�
CreateCustomerClient=.google.ads.googleads.v8.services.CreateCustomerClientRequest>.google.ads.googleads.v8.services.CreateCustomerClientResponse"[���7"2/v8/customers/{customer_id=*}:createCustomerClient:*�Acustomer_id,customer_clientE�Agoogleads.googleapis.com�A\'https://www.googleapis.com/auth/adwordsB�
$com.google.ads.googleads.v8.servicesBCustomerServiceProtoPZHgoogle.golang.org/genproto/googleapis/ads/googleads/v8/services;services�GAA� Google.Ads.GoogleAds.V8.Services� Google\\Ads\\GoogleAds\\V8\\Services�$Google::Ads::GoogleAds::V8::Servicesbproto3'
        , true);
        static::$is_initialized = true;
    }
}

