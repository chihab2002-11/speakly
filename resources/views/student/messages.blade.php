<x-layouts.student :user="$user" currentRoute="student.messages" pageTitle="Messages">
    {{-- Main Content Area - Three Column Layout --}}
    <div class="flex h-full" style="min-height: calc(100vh - 65px);">
        
        {{-- Left Column: Contacts Sidebar --}}
        <div class="flex flex-col w-72 h-full border-r" style="background-color: #F0F5EE; border-color: rgba(190, 201, 191, 0.2);">
            <div class="flex flex-col p-6 gap-6">
                {{-- Messages Heading --}}
                <h2 class="text-2xl font-bold tracking-tight" style="color: #181D19; letter-spacing: -0.6px;">
                    Messages
                </h2>
                
                {{-- Search Input --}}
                <div class="relative">
                    <div class="absolute left-3 top-1/2 -translate-y-1/2">
                        <svg class="w-[18px] h-[18px]" fill="#6F7A71" viewBox="0 0 24 24">
                            <path d="M15.5 14h-.79l-.28-.27C15.41 12.59 16 11.11 16 9.5 16 5.91 13.09 3 9.5 3S3 5.91 3 9.5 5.91 16 9.5 16c1.61 0 3.09-.59 4.23-1.57l.27.28v.79l5 4.99L20.49 19l-4.99-5zm-6 0C7.01 14 5 11.99 5 9.5S7.01 5 9.5 5 14 7.01 14 9.5 11.99 14 9.5 14z"/>
                        </svg>
                    </div>
                    <input 
                        type="text" 
                        placeholder="Search conversations..."
                        class="w-full h-[38px] pl-10 pr-4 text-sm rounded-lg border"
                        style="background: #FFFFFF; border-color: rgba(190, 201, 191, 0.3); color: #181D19;"
                    >
                </div>
                
                {{-- Contact Categories --}}
                <div class="flex flex-col gap-8">
                    {{-- Teachers Category --}}
                    <div class="flex flex-col gap-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider" style="color: #6F7A71; letter-spacing: 1.1px;">
                            Teachers
                        </span>
                        
                        <div class="flex flex-col gap-3">
                            {{-- Active Teacher Contact (Prof. Elena) --}}
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl" style="background: #DFE4DD;">
                                <div class="relative flex-shrink-0">
                                    <img 
                                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face" 
                                        alt="Prof. Elena Vance"
                                        class="w-11 h-11 rounded-full object-cover"
                                    >
                                    {{-- Online Indicator --}}
                                    <div class="absolute bottom-0 right-0 w-3 h-3 rounded-full border-2" style="background: #006A41; border-color: #FFFFFF;"></div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-semibold truncate" style="color: #181D19;">Prof. Elena ...</h4>
                                    <p class="text-xs truncate" style="color: #3F4941;">About syntax ...</p>
                                </div>
                                <span class="text-[10px] flex-shrink-0" style="color: #6F7A71;">10:42 AM</span>
                            </a>
                            
                            {{-- Dr. Julian Contact --}}
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-100 transition-colors">
                                <div class="relative flex-shrink-0">
                                    <img 
                                        src="https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=100&h=100&fit=crop&crop=face" 
                                        alt="Dr. Julian Torres"
                                        class="w-11 h-11 rounded-full object-cover"
                                    >
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium truncate" style="color: #181D19;">Dr. Julian T...</h4>
                                    <p class="text-xs truncate" style="color: #6F7A71;">Enrollment co...</p>
                                </div>
                                <span class="text-[10px] flex-shrink-0" style="color: #6F7A71;">Yesterday</span>
                            </a>
                        </div>
                    </div>
                    
                    {{-- Administration Category --}}
                    <div class="flex flex-col gap-4">
                        <span class="text-[11px] font-bold uppercase tracking-wider" style="color: #6F7A71; letter-spacing: 1.1px;">
                            Administration
                        </span>
                        
                        <div class="flex flex-col gap-3">
                            {{-- Registrar Office --}}
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-100 transition-colors">
                                <div class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center" style="background: #5E70BB;">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 7V3H2v18h20V7H12zM6 19H4v-2h2v2zm0-4H4v-2h2v2zm0-4H4V9h2v2zm0-4H4V5h2v2zm4 12H8v-2h2v2zm0-4H8v-2h2v2zm0-4H8V9h2v2zm0-4H8V5h2v2zm10 12h-8v-2h2v-2h-2v-2h2v-2h-2V9h8v10zm-2-8h-2v2h2v-2zm0 4h-2v2h2v-2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium truncate" style="color: #181D19;">Registrar Office</h4>
                                    <p class="text-xs truncate" style="color: #6F7A71;"></p>
                                </div>
                            </a>
                            
                            {{-- Secretary --}}
                            <a href="#" class="flex items-center gap-3 p-3 rounded-xl hover:bg-gray-100 transition-colors">
                                <div class="flex-shrink-0 w-11 h-11 rounded-full flex items-center justify-center" style="background: #C1E6CC;">
                                    <svg class="w-5 h-[18px]" fill="#476853" viewBox="0 0 24 24">
                                        <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.89 2 1.99 2H20c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-sm font-medium truncate" style="color: #181D19;">Secretary</h4>
                                    <p class="text-xs truncate" style="color: #6F7A71;"></p>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        {{-- Center Column: Active Chat View --}}
        <div class="flex flex-col flex-1" style="background: #FFFFFF;">
            {{-- Chat Header --}}
            <div class="flex items-center justify-between px-8 h-20 border-b" style="border-color: rgba(190, 201, 191, 0.1);">
                <div class="flex items-center gap-4">
                    <img 
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face" 
                        alt="Prof. Elena Vance"
                        class="w-10 h-10 rounded-full object-cover"
                    >
                    <div class="flex flex-col">
                        <h3 class="text-base font-bold" style="color: #181D19;">Prof. Elena Vance</h3>
                        <div class="flex items-center gap-1">
                            <div class="w-1.5 h-1.5 rounded-full" style="background: #006A41;"></div>
                            <span class="text-xs" style="color: #006A41;">Active Now</span>
                        </div>
                    </div>
                </div>
                
                {{-- Action Buttons --}}
                <div class="flex items-center gap-2">
                    <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-5 h-4" fill="#6F7A71" viewBox="0 0 24 24">
                            <path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/>
                        </svg>
                    </button>
                    <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-[18px] h-[18px]" fill="#6F7A71" viewBox="0 0 24 24">
                            <path d="M6.62 10.79c1.44 2.83 3.76 5.14 6.59 6.59l2.2-2.2c.27-.27.67-.36 1.02-.24 1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20c0 .55-.45 1-1 1-9.39 0-17-7.61-17-17 0-.55.45-1 1-1h3.5c.55 0 1 .45 1 1 0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/>
                        </svg>
                    </button>
                    <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors">
                        <svg class="w-1 h-4" fill="#6F7A71" viewBox="0 0 4 16">
                            <circle cx="2" cy="2" r="2"/>
                            <circle cx="2" cy="8" r="2"/>
                            <circle cx="2" cy="14" r="2"/>
                        </svg>
                    </button>
                </div>
            </div>
            
            {{-- Messages Canvas --}}
            <div class="flex-1 overflow-y-auto px-8 py-8">
                {{-- Date Divider --}}
                <div class="flex items-center gap-4 py-4 mb-6">
                    <div class="flex-1 h-px" style="background: rgba(190, 201, 191, 0.2);"></div>
                    <span class="text-[10px] font-bold uppercase tracking-wider" style="color: #6F7A71; letter-spacing: 1px;">
                        Monday, Oct 23
                    </span>
                    <div class="flex-1 h-px" style="background: rgba(190, 201, 191, 0.2);"></div>
                </div>
                
                {{-- Teacher Message Group --}}
                <div class="flex items-end gap-3 mb-6 max-w-[400px]">
                    <img 
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face" 
                        alt="Prof. Elena Vance"
                        class="w-8 h-8 rounded-full object-cover mb-5 flex-shrink-0"
                    >
                    <div class="flex flex-col gap-2">
                        <div class="p-4 rounded-2xl rounded-bl-none" style="background: #F0F5EE; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
                            <p class="text-sm leading-relaxed" style="color: #181D19; line-height: 23px;">
                                Good morning! I've reviewed your latest assignment on advanced syntax. Your use of conditional structures is excellent.
                            </p>
                        </div>
                        <div class="p-4 rounded-2xl rounded-bl-none" style="background: #F0F5EE; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
                            <p class="text-sm leading-relaxed" style="color: #181D19; line-height: 23px;">
                                I've attached a guide that covers the more complex grammar exercises we'll tackle in the next module. Take a look at the section on subjunctive mood.
                            </p>
                        </div>
                        <span class="text-[10px] ml-1" style="color: #6F7A71;">10:12 AM</span>
                    </div>
                </div>
                
                {{-- Student Message Group --}}
                <div class="flex flex-col items-end gap-2 mb-6 ml-auto max-w-[350px]">
                    <div class="p-4 rounded-2xl rounded-br-none" style="background: #006A41; box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.1), 0px 2px 4px -2px rgba(0, 0, 0, 0.1);">
                        <p class="text-sm leading-relaxed text-white" style="line-height: 23px;">
                            Thank you, Professor! I was actually struggling a bit with the subjunctive mood in the last practice test. This guide will be very helpful.
                        </p>
                    </div>
                    <div class="p-4 rounded-2xl rounded-br-none" style="background: #006A41; box-shadow: 0px 4px 6px -1px rgba(0, 0, 0, 0.1), 0px 2px 4px -2px rgba(0, 0, 0, 0.1);">
                        <p class="text-sm leading-relaxed text-white" style="line-height: 23px;">
                            Should I complete the grammar exercises in the guide before Wednesday's lecture?
                        </p>
                    </div>
                    <span class="text-[10px] mr-1" style="color: #6F7A71;">10:35 AM</span>
                </div>
                
                {{-- Teacher Response --}}
                <div class="flex items-end gap-3 mb-6 max-w-[400px]">
                    <img 
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=100&h=100&fit=crop&crop=face" 
                        alt="Prof. Elena Vance"
                        class="w-8 h-8 rounded-full object-cover mb-5 flex-shrink-0"
                    >
                    <div class="flex flex-col gap-2">
                        <div class="p-4 rounded-2xl rounded-bl-none" style="background: #F0F5EE; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);">
                            <p class="text-sm leading-relaxed" style="color: #181D19; line-height: 23px;">
                                Yes, focusing on exercises 3 through 7 would be ideal. We will go through the answers together in class.
                            </p>
                        </div>
                        <span class="text-[10px] ml-1" style="color: #6F7A71;">10:42 AM</span>
                    </div>
                </div>
            </div>
            
            {{-- Message Input --}}
            <div class="p-6 border-t" style="background: #FFFFFF; border-color: rgba(190, 201, 191, 0.1);">
                <div class="flex items-center gap-2 p-2 rounded-2xl border" style="background: rgba(255, 255, 255, 0.8); border-color: rgba(190, 201, 191, 0.3); backdrop-filter: blur(12px);">
                    {{-- Attachment Button --}}
                    <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0">
                        <svg class="w-5 h-5" fill="#6F7A71" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm5 11h-4v4h-2v-4H7v-2h4V7h2v4h4v2z"/>
                        </svg>
                    </button>
                    
                    {{-- Text Input --}}
                    <input 
                        type="text" 
                        placeholder="Type a message..."
                        class="flex-1 py-2 px-3 text-sm bg-transparent focus:outline-none"
                        style="color: #181D19;"
                    >
                    
                    {{-- Emoji Button --}}
                    <button class="p-2 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0">
                        <svg class="w-5 h-5" fill="#6F7A71" viewBox="0 0 24 24">
                            <path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm3.5-9c.83 0 1.5-.67 1.5-1.5S16.33 8 15.5 8 14 8.67 14 9.5s.67 1.5 1.5 1.5zm-7 0c.83 0 1.5-.67 1.5-1.5S9.33 8 8.5 8 7 8.67 7 9.5 7.67 11 8.5 11zm3.5 6.5c2.33 0 4.31-1.46 5.11-3.5H6.89c.8 2.04 2.78 3.5 5.11 3.5z"/>
                        </svg>
                    </button>
                    
                    {{-- Send Button --}}
                    <button 
                        class="px-6 py-2 rounded-xl text-sm font-semibold text-white flex-shrink-0"
                        style="background: #006A41; box-shadow: 0px 1px 2px rgba(0, 0, 0, 0.05);"
                    >
                        Send
                    </button>
                </div>
            </div>
        </div>
        
        {{-- Right Column: Profile/File Inspector --}}
        <div class="flex flex-col w-64 h-full border-l" style="background: #F0F5EE; border-color: rgba(190, 201, 191, 0.2);">
            <div class="flex flex-col items-center p-6 gap-8">
                {{-- Profile Summary --}}
                <div class="flex flex-col items-center gap-3">
                    <img 
                        src="https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=200&h=200&fit=crop&crop=face" 
                        alt="Prof. Elena Vance"
                        class="w-24 h-24 rounded-full object-cover border-4"
                        style="border-color: #DFE4DD;"
                    >
                    <div class="flex flex-col items-center text-center">
                        <h3 class="text-lg font-bold" style="color: #181D19;">Prof. Elena Vance</h3>
                        <p class="text-sm" style="color: #6F7A71;">Senior Linguistics Department</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.student>
