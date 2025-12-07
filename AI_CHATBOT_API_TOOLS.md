# AI Chatbot API Tools

## Overview
This document describes the AI chatbot endpoint that uses Gemini AI with integrated tools to access your API endpoints.

## Endpoint
```
GET /api/test-chat/{message}
```

## Parameters
- `message` (required): The user's message or question

## Example Usage

### Simple Question
```
GET /api/test-chat/ما هي المدن المتاحة؟
```

### Complex Question with Tool Usage
```
GET /api/test-chat/ابحث لي عن فندق في الرياض وأعطني تفاصيله
```

```
GET /api/test-chat/كم تكلفة حجز غرفة لشخصين من تاريخ 2025-12-15 إلى 2025-12-20؟
```

```
GET /api/test-chat/ما هي الرحلات المتاحة في فئة المغامرات؟
```

## Available Tools

The AI has access to the following tools (API endpoints):

### Hotels
1. **get_all_hotels** - Get list of all available hotels
   - Parameters: city_id (optional), hotel_type_id (optional)

2. **get_hotel_details** - Get detailed information about a specific hotel
   - Parameters: hotel_id (required)

3. **get_cheapest_room** - Get the cheapest available room for a hotel
   - Parameters: hotel_id (required)

### Rooms
4. **get_all_rooms** - Get list of all available rooms
   - Parameters: hotel_id (optional)

5. **get_room_details** - Get detailed information about a specific room
   - Parameters: room_id (required)

6. **calculate_room_booking_price** - Calculate booking price
   - Parameters: room_id, check_in (YYYY-MM-DD), check_out (YYYY-MM-DD), guests

### Trips
7. **get_all_trips** - Get list of all available trips
   - Parameters: category_id (optional), sub_category_id (optional)

8. **get_trip_details** - Get detailed information about a trip
   - Parameters: trip_id (required)

9. **calculate_trip_booking_price** - Calculate trip booking price
   - Parameters: trip_id, booking_date (YYYY-MM-DD), guests

### Data
10. **get_hotel_types** - Get list of hotel types
11. **get_cities** - Get list of cities
12. **get_categories** - Get list of trip categories
13. **get_sub_categories** - Get list of trip sub-categories
14. **get_booking_status** - Get list of booking status options

### FAQs
15. **get_faqs** - Get list of FAQs
16. **get_faq_details** - Get specific FAQ details
    - Parameters: faq_id (required)

## Response Format

### Success Response
```json
{
    "success": true,
    "message": "AI response text here...",
    "usage": {
        "input_tokens": 123,
        "output_tokens": 456
    },
    "tool_calls": [...],
    "steps": [...]
}
```

### Error Responses

#### Rate Limit (429)
```json
{
    "success": false,
    "error": "Rate limit exceeded. Please try again later.",
    "details": "..."
}
```

#### Server Error (500)
```json
{
    "success": false,
    "error": "AI service error occurred.",
    "details": "..."
}
```

## How It Works

1. User sends a message to the endpoint
2. The AI (Gemini 2.0 Flash) analyzes the message
3. If needed, the AI calls one or more tools to fetch data from your API
4. The AI processes the data and formulates a response
5. The response is returned to the user in Arabic or the requested language

## Example Scenarios

### Scenario 1: Hotel Search
**User:** "أريد فندق في جدة"

**AI Actions:**
1. Calls `get_cities` to find Jeddah's ID
2. Calls `get_all_hotels` with city_id
3. Returns list of hotels in Jeddah

### Scenario 2: Price Calculation
**User:** "كم سعر الغرفة رقم 5 لمدة 3 ليالي من 15 ديسمبر؟"

**AI Actions:**
1. Calls `calculate_room_booking_price` with:
   - room_id: 5
   - check_in: 2025-12-15
   - check_out: 2025-12-18
   - guests: 2 (assumes default)
2. Returns calculated price

### Scenario 3: Trip Information
**User:** "ما هي رحلات المغامرات المتوفرة؟"

**AI Actions:**
1. Calls `get_categories` to find adventure category ID
2. Calls `get_all_trips` with category_id
3. Returns list of adventure trips

## Notes

- The AI uses Gemini 2.0 Flash model
- All tools use GET requests only (no authentication required)
- The AI can chain multiple tool calls to answer complex questions
- Responses include token usage information for monitoring
- The system handles rate limiting and errors gracefully

