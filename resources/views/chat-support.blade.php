<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Conversational Support') }}
            </h2>
            <a href="#" class="inline-flex items-center px-6 py-3 bg-blue-600 border border-transparent rounded-md font-semibold text-sm text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150 shadow-lg">
                Start Conversational Support
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 space-y-6">
                    <div class="mb-8">
                        <h3 class="text-2xl font-bold mb-4 text-blue-700 text-center">Welcome to UniPulse Conversational Support</h3>
                        <p class="text-gray-600 mb-6 text-center">
                            Get immediate assistance with your academic queries, personal problems, or general university-related questions through our dedicated chat support system.
                        </p>
                        <div class="bg-blue-50 p-6 rounded-lg border-l-4 border-blue-500 mb-6">
                            <h4 class="text-lg font-semibold mb-3 text-blue-800">Benefits of UniPulse Conversational Support</h4>
                            <p class="text-gray-700 leading-relaxed">
                                Experience the advantage of instant, personalized support that adapts to your needs. Our conversational support system offers 24/7 accessibility, faster response times, and seamless communication. You'll benefit from detailed problem-solving, immediate access to resources, and the ability to multitask while getting help. Whether you need academic guidance, or administrative support, our platform ensures your queries are addressed efficiently, saving you valuable time and reducing stress. Plus, all conversations are documented for future reference, helping you keep track of important information and solutions provided.
                            </p>
                        </div>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="border border-blue-200 rounded-lg p-6">
                            <h4 class="text-lg font-semibold mb-3 text-blue-700">How It Works</h4>
                            <ul class="list-disc list-inside space-y-2 text-gray-600">
                                <li>Click the "Start Conversational Support" button above</li>
                                <li>Enter your topic of concern</li>
                                <li>Get real-time assistance for your queries</li>
                            </ul>
                        </div>

                        <div class="border border-blue-200 rounded-lg p-6">
                            <h4 class="text-lg font-semibold mb-3 text-blue-700">Available Support Areas</h4>
                            <ul class="list-disc list-inside space-y-2 text-gray-600">
                                <li>Academic Guidance</li>
                                <li>Mental health guidance</li>
                                <li>Administrative Queries</li>
                                <li>And many more...</li>
                            </ul>
                        </div>
                    </div>

                    <div class="mt-8 bg-blue-50 p-6 rounded-lg">
                        <h4 class="text-lg font-semibold mb-3 text-blue-700">Support Hours</h4>
                        <p class="text-gray-600">
                            Our support team is available 24/7 accessibility. 
                            During off-hours, you can leave a message and get prompt replies.
                        </p>
                    </div>

                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="border border-blue-200 rounded-lg p-6">
                            <h4 class="text-lg font-semibold mb-3 text-blue-700">System Features</h4>
                            <ul class="list-disc list-inside space-y-2 text-gray-600">
                                <li>Secure end-to-end encryption for all conversations</li>
                                <li>Automatic chat history saving for future reference</li>
                                <li>Quick response time with AI-powered suggestions</li>
                            </ul>
                        </div>

                        <div class="border border-blue-200 rounded-lg p-6">
                            <h4 class="text-lg font-semibold mb-3 text-blue-700">Technical Requirements</h4>
                            <ul class="list-disc list-inside space-y-2 text-gray-600">
                                <li>Compatible with all modern web browsers</li>
                                <li>Stable internet connection required for optimal performance</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>