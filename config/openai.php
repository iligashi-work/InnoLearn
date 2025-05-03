<?php
// Function to generate local insights without API
function generateLocalInsights($data, $type) {
    if (!is_array($data) || empty($data)) {
        return "No data available for analysis.";
    }
    
    $insights = [];
    
    // Sort data by count
    usort($data, function($a, $b) {
        return $b['count'] - $a['count'];
    });
    
    // Generate basic insights based on the data
    // Insight 1: Top category
    $top = $data[0];
    $category_key = ($type == 'department distribution') ? 'department' : 'category';
    $insights[] = "The highest count is in " . $top[$category_key] . " with " . $top['count'] . " entries.";
    
    // Insight 2: Distribution
    $total = array_sum(array_column($data, 'count'));
    if ($total > 0) {
        $top_percentage = round(($top['count'] / $total) * 100, 1);
        $insights[] = "The top category represents " . $top_percentage . "% of the total.";
    }
    
    // Insight 3: Number of categories
    $category_type = ($type == 'department distribution') ? 'departments' : 'categories';
    $insights[] = "There are " . count($data) . " different " . $category_type . " in the data.";
    
    return implode("\n", $insights);
}

// Function to make API calls to DeepSeek
function makeDeepSeekRequest($prompt, $type) {
    try {
        $api_key = 'sk-cfc3614b13af48d5a27f30a0878ad1f1';
        $url = 'https://api.deepseek.com/v1/chat/completions';
        
        $headers = [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $api_key,
            'Accept: application/json'
        ];
        
        $data = [
            'model' => 'deepseek-chat',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are an analytics expert. Provide concise, actionable insights.'
                ],
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ],
            'temperature' => 0.7,
            'max_tokens' => 150
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            $result = json_decode($response, true);
            if (isset($result['choices'][0]['message']['content'])) {
                return $result['choices'][0]['message']['content'];
            }
        }
        
        // If API fails, fall back to local insights
        $data_array = json_decode($prompt, true);
        if (is_array($data_array)) {
            return generateLocalInsights($data_array, $type);
        }
        return generateLocalInsights([], $type);
        
    } catch (Exception $e) {
        // If any error occurs, fall back to local insights
        $data_array = json_decode($prompt, true);
        if (is_array($data_array)) {
            return generateLocalInsights($data_array, $type);
        }
        return generateLocalInsights([], $type);
    }
}

// Function to generate insights
function getDeepSeekInsights($data, $type) {
    try {
        if (!is_array($data) || empty($data)) {
            return "No data available for analysis.";
        }
        
        $prompt = "Analyze the following $type data and provide 3 key insights: " . json_encode($data);
        return makeDeepSeekRequest($prompt, $type);
    } catch (Exception $e) {
        error_log("Error in getDeepSeekInsights: " . $e->getMessage());
        return generateLocalInsights($data, $type);
    }
} 