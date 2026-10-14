/**
 * VUT MainRes Maintenance - Internationalization (i18n) Engine
 * Supported Languages:
 *   - 'en': English (Default)
 *   - 'ts': Xitsonga (Tsonga)
 *   - 'zu': isiZulu (Zulu)
 *
 * Provides real-time dynamic in-place DOM translation, persistent preferences
 * (via both localStorage and cookies), auto-synchronization with Settings
 * dropdowns, and automatic mutation monitoring for dynamic content.
 */

(function (window, document) {
    'use strict';

    // Comprehensive Dictionary for VUT MainRes Maintenance System
    const DICTIONARY = {
        // Navigation & Brand
        "VUT MainRes": { ts: "VUT MainRes", zu: "I-VUT MainRes" },
        "Maintenance": { ts: "Vutshila / Nkululeko", zu: "Ukulungisa" },
        "MAINTENANCE SERVICES": { ts: "VUKORHOKERI BYO LUNGHISA", zu: "IZINSIZA ZOKULUNGISA" },
        "Dashboard": { ts: "Dashibodo", zu: "Ideshibhodi" },
        "My Dashboard": { ts: "Dashibodo ya Mina", zu: "Ideshibhodi Yami" },
        "Admin Dashboard": { ts: "Dashibodo ya Mulanguteri", zu: "Ideshibhodi Yomlawuli" },
        "Staff Dashboard": { ts: "Dashibodo ya Mutirhi", zu: "Ideshibhodi Yomsebenzi" },
        "My Issues": { ts: "Swiphiqo Swa Mina", zu: "Izinkinga Zami" },
        "All Issues": { ts: "Swiphiqo Hinkwaswo", zu: "Zonke Izinkinga" },
        "Manage Issues": { ts: "Lawula Swiphiqo", zu: "Phatha Izinkinga" },
        "Report Issue": { ts: "Bika Xiphiqo", zu: "Bika Inkinga" },
        "Report an Issue": { ts: "Bika Xiphiqo", zu: "Bika Inkinga" },
        "Report Maintenance Issue": { ts: "Bika Xiphiqo xa ku Lunghisa", zu: "Bika Inkinga Yokulungisa" },
        "My Profile": { ts: "Phurofayili Ya Mina", zu: "Iphrofayili Yami" },
        "Profile": { ts: "Phurofayili", zu: "Iphrofayili" },
        "Settings": { ts: "Swiyimiso", zu: "Izilungiselelo" },
        "Back to Home": { ts: "Tlhelela eKaya", zu: "Buyela Ekhaya" },
        "Home": { ts: "Kaya", zu: "Ikhaya" },
        "About": { ts: "Mayelana", zu: "Mayelana Nathi" },
        "Report": { ts: "Bika", zu: "Bika" },
        "Log Out": { ts: "Huma", zu: "Phuma" },
        "Sign Out": { ts: "Huma", zu: "Phuma" },
        "Log In": { ts: "Nghena", zu: "Ngena" },
        "Sign In": { ts: "Nghena", zu: "Ngena" },
        "Sign Up": { ts: "Tsarisa", zu: "Bhalisa" },
        "Register": { ts: "Tsarisa", zu: "Bhalisa" },
        "Students": { ts: "Vadyondzi", zu: "Abafundi" },
        "Maintenance Staff": { ts: "Vatshila va Nkululeko", zu: "Abasebenzi Bokulungisa" },
        "Assigned Issues": { ts: "Swiphiqo leswi Nyikiweke", zu: "Izinkinga Ezinikeziwe" },
        "Overview": { ts: "Vumbhoni", zu: "Ukubuka Konke" },
        "Manage Students": { ts: "Lawula Vadyondzi", zu: "Phatha Abafundi" },
        "Manage Staff": { ts: "Lawula Vatirhi", zu: "Phatha Abasebenzi" },

        // User Roles
        "Student": { ts: "Mudyondzi", zu: "Umfundi" },
        "Staff": { ts: "Mutirhi", zu: "Umsebenzi" },
        "Staff Member": { ts: "Xirho xa Vatirhi", zu: "Ilungu Labasebenzi" },
        "Admin": { ts: "Mulanguteri", zu: "Umlawuli" },
        "Administrator": { ts: "Mulanguteri", zu: "Umlawuli" },
        "System Administrator": { ts: "Mulanguteri wa Sisteme", zu: "Umlawuli Wesistimu" },
        "Role: Student": { ts: "Xiyimo: Mudyondzi", zu: "Indima: Umfundi" },
        "Role: System Administrator": { ts: "Xiyimo: Mulanguteri wa Sisteme", zu: "Indima: Umlawuli Wesistimu" },
        "Role: Maintenance Staff": { ts: "Xiyimo: Mutirhi wo Lunghisa", zu: "Indima: Umsebenzi Wokulungisa" },

        // Settings Page
        "Language": { ts: "Ririmi", zu: "Ulimi" },
        "Interface Language": { ts: "Ririmi ra Sisteme", zu: "Ulimi Lwesistimu" },
        "Choose your preferred display language.": { ts: "Hlawula ririmi leri u ri tsakelaka ku ri tirhisa.", zu: "Khetha ulimi oluthandayo lokubonisa." },
        "English": { ts: "English", zu: "English" },
        "Xitsonga": { ts: "Xitsonga", zu: "Xitsonga" },
        "isiZulu": { ts: "isiZulu", zu: "isiZulu" },
        "Appearance": { ts: "Kuvoneka", zu: "Ukubonakala" },
        "Light Mode": { ts: "Mudyondzo wo Vangama", zu: "Imodi Yokukhanya" },
        "Dark Mode": { ts: "Mudyondzo wa Xinyami", zu: "Imodi Emnyama" },
        "Toggle between dark (default) and light interface theme.": { ts: "Cinca exikarhi ka xitayele xo dzwihala na xo vangama.", zu: "Shintsha phakathi kwemodi emnyama nekhanyayo." },
        "Toggle between dark (default) and light interface theme. This applies to all dashboards.": { ts: "Cinca exikarhi ka xitayele xo dzwihala na xo vangama. Leswi swi tirha eka tidashibodo hinkwato.", zu: "Shintsha phakathi kwemodi emnyama nekhanyayo. Lokhu kusebenza kuwo wonke amadeshibhodi." },
        "Account": { ts: "Akhawunti", zu: "I-akhawunti" },
        "Change Password": { ts: "Cinca Phasiwedi", zu: "Shintsha Iphasiwedi" },
        "Update your account password regularly.": { ts: "Pfuxeta phasiwedi ya akhawunti ya wena nkarhi na nkarhi.", zu: "Buyekeza iphasiwedi ye-akhawunti yakho njalo." },
        "Update your admin account password regularly.": { ts: "Pfuxeta phasiwedi ya wena ya mulanguteri nkarhi na nkarhi.", zu: "Buyekeza iphasiwedi yakho yomlawuli njalo." },
        "Update your staff account password regularly.": { ts: "Pfuxeta phasiwedi ya akhawunti ya wena ya mutirhi nkarhi na nkarhi.", zu: "Buyekeza iphasiwedi ye-akhawunti yabasebenzi njalo." },
        "Current Session": { ts: "Nkarhi wa Sweswi", zu: "Iseshini Yamanje" },
        "Signed in as": { ts: "U nghenile tanihi", zu: "Ungene njenge" },
        "System Status:": { ts: "Xiyimo xa Sisteme:", zu: "Isimo Sesistimu:" },
        "System Status": { ts: "Xiyimo xa Sisteme", zu: "Isimo Sesistimu" },
        "Operational": { ts: "Ya Tirha", zu: "Iyasebenza" },
        "Settings last updated:": { ts: "Swiyimiso swi pfuxetiwe ro hetelela:", zu: "Izilungiselelo zigcine ukubuyekezwa:" },
        "Never": { ts: "A swi si pfuxetiwa", zu: "Akukaze" },
        "Current Password": { ts: "Phasiwedi ya Sweswi", zu: "Iphasiwedi Yamanje" },
        "New Password": { ts: "Phasiwedi Yintshwa", zu: "Iphasiwedi Entsha" },
        "Confirm New Password": { ts: "Tiyisisa Phasiwedi Yintshwa", zu: "Qinisekisa Iphasiwedi Entsha" },
        "Enter your current password": { ts: "Nghenisa phasiwedi ya wena ya sweswi", zu: "Faka iphasiwedi yakho yamanje" },
        "Min. 8 characters": { ts: "Kutsongo swinene tindzimana ta 8", zu: "Ubuncane bezinhlamvu ezingu-8" },
        "Re-enter new password": { ts: "Nghenisa nakambe phasiwedi yintshwa", zu: "Faka futhi iphasiwedi entsha" },
        "Passwords are securely hashed before storage.": { ts: "Tiphasiwedi ti hlayisiwa hi vuhlayiseki lebyikulu.", zu: "Amaphasiwedi avikelwe ngokuphepha ngaphambi kokugcinwa." },
        "Cancel": { ts: "Khansela", zu: "Khansela" },
        "Update Password": { ts: "Pfuxeta Phasiwedi", zu: "Buyekeza Iphasiwedi" },
        "Change Admin Password": { ts: "Cinca Phasiwedi ya Mulanguteri", zu: "Shintsha Iphasiwedi Yomlawuli" },
        "Change Staff Password": { ts: "Cinca Phasiwedi ya Mutirhi", zu: "Shintsha Iphasiwedi Yomsebenzi" },
        "Sign out of student panel?": { ts: "U lava ku huma eka panela ya mudyondzi?", zu: "Ufuna ukuphuma kuphaneli yomfundi?" },
        "Sign out of admin panel?": { ts: "U lava ku huma eka panela ya mulanguteri?", zu: "Ufuna ukuphuma kuphaneli yomlawuli?" },
        "Sign out of maintenance panel?": { ts: "U lava ku huma eka panela ya vutirheli?", zu: "Ufuna ukuphuma kuphaneli yokulungisa?" },

        // Toast & Notification Messages
        "Language preference saved": { ts: "Kucinca ka ririmi ku hlayisiwile", zu: "Ukukhetha kolimi kulondoloziwe" },
        "Light mode enabled": { ts: "Mudyondzo wo vangama wu pfuriwile", zu: "Imodi yokukhanya ivuliwe" },
        "Dark mode enabled": { ts: "Mudyondzo wa xinyami wu pfuriwile", zu: "Imodi emnyama ivuliwe" },
        "New password must be at least 8 characters": { ts: "Phasiwedi yintshwa yi fanele ku va na tindzimana ta 8", zu: "Iphasiwedi entsha kumele ibe nezinhlamvu okungenani eziyi-8" },
        "Passwords do not match": { ts: "Tiphasiwedi a ti fani", zu: "Amaphasiwedi awafani" },
        "Password changed successfully": { ts: "Phasiwedi yi cinciwile hi ndlela leyinene", zu: "Iphasiwedi ishintshwe ngempumelelo" },
        "Profile updated successfully": { ts: "Phurofayili yi pfuxetiwile hi ndlela leyinene", zu: "Iphrofayili ibuyekezwe ngempumelelo" },
        "Issue reported successfully": { ts: "Xiphiqo xi bikiwile hi ndlela leyinene", zu: "Inkinga ibikiwe ngempumelelo" },
        "Issue status updated successfully": { ts: "Xiyimo xa xiphiqo xi pfuxetiwile", zu: "Isimo senkinga sibuyekezwe ngempumelelo" },

        // Dashboard Metrics & Headers
        "Total Issues": { ts: "Swiphiqo Hinkwaswo", zu: "Izinkinga Sezizonke" },
        "Pending": { ts: "Swi Yimerile", zu: "Kusalindwe" },
        "In Progress": { ts: "Swa Endliwa", zu: "Kusaqhubeka" },
        "Completed": { ts: "Swi Hetiwile", zu: "Kuqediwe" },
        "Resolved": { ts: "Swi Lulamisiwile", zu: "Kuxazululiwe" },
        "Active Issues": { ts: "Swiphiqo leswi Tirhaka", zu: "Izinkinga Ezisebenzayo" },
        "Recent Issues": { ts: "Swiphiqo swa Sweswi", zu: "Izinkinga Zakamuva" },
        "Quick Actions": { ts: "Swiendlo swa Xihatla", zu: "Izenzo Ezisheshayo" },
        "View All": { ts: "Vona Hinkwaswo", zu: "Buka Konke" },
        "View Details": { ts: "Vona Vuxokoxoko", zu: "Buka Imininingwane" },
        "Track and manage your maintenance requests": { ts: "Landelerisa no lawula swikombelo swa wena swo lunghisa", zu: "Landelela uphinde uphathe izicelo zakho zokulungisa" },
        "Here's what's happening with your reported maintenance issues.": { ts: "Lawa i mahungu ya swiphiqo leswi u swi bikile.", zu: "Nawa amaphuzu mayelana nezinkinga ozibikile." },
        "Maintenance Overview": { ts: "Nkatsakanyo wo Lunghisa", zu: "Ukubuka Konke Kokulungisa" },
        "System Overview": { ts: "Nkatsakanyo wa Sisteme", zu: "Ukubuka Konke Kwesistimu" },
        "No issues reported yet": { ts: "A ku si bikiwa xiphiqo", zu: "Azikho izinkinga ezibikiwe okwamanje" },
        "No issues found": { ts: "A ku kumekanga swiphiqo", zu: "Azikho izinkinga ezitholiwe" },
        "No issues found matching your filters": { ts: "A ku na swiphiqo leswi fambelanaka na leswi u swi lavaka", zu: "Azikho izinkinga ezihambisana nalokho okuhlungile" },
        "Welcome back": { ts: "Kwamukeleka nakambe", zu: "Siyakwamukela futhi" },
        "Welcome back,": { ts: "Kwamukeleka nakambe,", zu: "Siyakwamukela futhi," },
        "Welcome back! Here is the latest maintenance update.": { ts: "Kwamukeleka nakambe! Lawa i mahungu mantshwa yo lunghisa.", zu: "Siyakwamukela futhi! Nawa amaphuzu akamuva okuxazulula izinkinga." },

        // Issue Table & Fields
        "Issue ID": { ts: "ID ya Xiphiqo", zu: "I-ID Yenkinga" },
        "Title": { ts: "Nhlokomhaka", zu: "Isihloko" },
        "Issue Title": { ts: "Nhlokomhaka ya Xiphiqo", zu: "Isihloko Senkinga" },
        "Category": { ts: "Xiyenge", zu: "Umkhakha" },
        "Issue Category": { ts: "Xiyenge xa Xiphiqo", zu: "Umkhakha Wenkinga" },
        "Residence": { ts: "Residense", zu: "Indawo Yokuhlala" },
        "Room": { ts: "Kamara", zu: "Ikamelo" },
        "Room Number": { ts: "Nomboro ya Kamara", zu: "Inombolo Yekamelo" },
        "Room NO": { ts: "Nomboro ya Kamara", zu: "Inombolo Yekamelo" },
        "Block": { ts: "Buloko", zu: "Ibhulokhi" },
        "Block NO": { ts: "Nomboro ya Buloko", zu: "Inombolo Yebhulokhi" },
        "Priority": { ts: "Xiyimo xo Rhangisa", zu: "Ubucayi" },
        "Priority Level": { ts: "Xiyimo xo Rhangisa", zu: "Izinga Lobucayi" },
        "Status": { ts: "Xiyimo", zu: "Isimo" },
        "Date": { ts: "Siku", zu: "Usuku" },
        "Date Reported": { ts: "Siku leri Bikiweke", zu: "Usuku Okubikwe Ngalo" },
        "Reported": { ts: "Bikiwile", zu: "Kubikiwe" },
        "Assigned To": { ts: "Nyikiwile Eka", zu: "Yabelwe Ku" },
        "Assigned Staff": { ts: "Mutirhi lweyi a Nyikiweke", zu: "Umsebenzi Ophiwe Umsebenzi" },
        "Actions": { ts: "Swa ku Endla", zu: "Izenzo" },
        "Action": { ts: "Xiendlo", zu: "Isenzo" },
        "Description": { ts: "Nhlamuselo", zu: "Incazelo" },
        "Issue Details": { ts: "Vuxokoxoko bya Xiphiqo", zu: "Imininingwane Yenkinga" },
        "Details": { ts: "Vuxokoxoko", zu: "Imininingwane" },
        "Close": { ts: "Pfalani", zu: "Vala" },
        "Save": { ts: "Hlayisa", zu: "Londoloza" },
        "Save Changes": { ts: "Hlayisa Kucinca", zu: "Londoloza Izinguquko" },
        "Delete": { ts: "Sula", zu: "Sula" },
        "Edit": { ts: "Lulamisa", zu: "Hlela" },
        "Filter": { ts: "Sefa", zu: "Hlunga" },
        "Search": { ts: "Lava", zu: "Sesha" },
        "Reset": { ts: "Tlherisela", zu: "Setha Kabusha" },
        "Search issues...": { ts: "Lava swiphiqo...", zu: "Sesha izinkinga..." },
        "All Statuses": { ts: "Swiyimo Hinkwaswo", zu: "Zonke Izimo" },
        "All Categories": { ts: "Tiyingiso Hinkwato", zu: "Zonke Izinhlobo" },
        "All Priorities": { ts: "Swiyimo Hinkwaswo swo Rhangisa", zu: "Wonke Amazinga Obucayi" },

        // Priorities
        "Low": { ts: "Hansi", zu: "Phansi" },
        "Medium": { ts: "Xikarhi", zu: "Phakathi" },
        "High": { ts: "Henhla", zu: "Phezulu" },
        "Urgent": { ts: "Xihatla", zu: "Kuphuthumayo" },
        "Low - Standard maintenance": { ts: "Hansi - Ku lunghisa ka ntolovelo", zu: "Phansi - Ukulungisa okujwayelekile" },
        "Medium - Normal priority": { ts: "Xikarhi - Xiyimo xa ntolovelo", zu: "Phakathi - Izinga elijwayelekile" },
        "High - Needs attention soon": { ts: "Henhla - Swi lava ku lunghisiwa hi ku hatlisa", zu: "Phezulu - Kudinga ukulungiswa maduze" },
        "Urgent - Emergency / Safety hazard": { ts: "Xihatla - Xiyimo xa xilamulela-mhangu", zu: "Kuphuthumayo - Ingozi / Isimo esibucayi" },

        // Categories & Problems
        "Plumbing": { ts: "Maphaphisa / Metsi", zu: "Amapayipi Amanzi" },
        "Electrical": { ts: "Gezi", zu: "Ugesi" },
        "Carpentry": { ts: "Vubetla", zu: "Ukubaza" },
        "Painting": { ts: "Pende", zu: "Ukupenda" },
        "Appliance": { ts: "Switirhisiwa", zu: "Izinto Zikagesi" },
        "Cleaning": { ts: "Ku Basisa", zu: "Ukuhlanza" },
        "Door / Lock": { ts: "Rivalo / Xilotlelo", zu: "Isicabha / Isikhiye" },
        "Window": { ts: "Fasitere", zu: "Ifasitela" },
        "General": { ts: "Swa Tiko Hinkwaro", zu: "Okujwayelekile" },
        "Other": { ts: "Swin'wana", zu: "Okunye" },
        "wifi": { ts: "Inthanete (Wi-Fi)", zu: "I-Wi-Fi" },
        "stove": { ts: "xitofu", zu: "isitofu" },
        "shower": { ts: "xihlambi", zu: "ishawa" },
        "door handle": { ts: "nhlokwana ya rivanti", zu: "isibambo sesicabha" },
        "window handle": { ts: "nhlokwana ya fasitere", zu: "isibambo sefasitela" },
        "leakage": { ts: "ku lutla ka metsi", zu: "ukuvuza kwamanzi" },
        "blocked sink": { ts: "sinki leyi pfalekeke", zu: "usinki ovalekile" },
        "plug": { ts: "phalaka ya gezi", zu: "ipulagi kagesi" },
        "bulb": { ts: "voni", zu: "ibhola lesibani" },
        "bed": { ts: "mubhedhe", zu: "umbhede" },
        "table": { ts: "tafula", zu: "itafula" },

        // Report Issue Integrated Form
        "Add Report": { ts: "Engetela Xiviko", zu: "Faka Umbiko" },
        "Remove Report": { ts: "Susa Xiviko", zu: "Susa Umbiko" },
        "Field Name": { ts: "Vito ra Xiyenge", zu: "Igama Lomkhakha" },
        "Report 1": { ts: "Xiviko 1", zu: "Umbiko 1" },
        "Report 2": { ts: "Xiviko 2", zu: "Umbiko 2" },
        "Report 3": { ts: "Xiviko 3", zu: "Umbiko 3" },
        "Student NO": { ts: "Nomboro ya Mudyondzi", zu: "Inombolo Yomfundi" },
        "Enter Student NO": { ts: "Nghenisa Nomboro ya Mudyondzi", zu: "Faka Inombolo Yomfundi" },
        "Select Residence": { ts: "Hlawula Residense", zu: "Khetha Indawo Yokuhlala" },
        "Select Block": { ts: "Hlawula Buloko", zu: "Khetha Ibhulokhi" },
        "Select Room": { ts: "Hlawula Kamara", zu: "Khetha Ikamelo" },
        "ISSUE": { ts: "XIPHIQO", zu: "INKINGA" },
        "Select Issue": { ts: "Hlawula Xiphiqo", zu: "Khetha Inkinga" },
        "Select Category": { ts: "Hlawula Xiyenge", zu: "Khetha Umkhakha" },
        "Select Priority": { ts: "Hlawula Xiyimo xo Rhangisa", zu: "Khetha Ubucayi" },
        "Gender": { ts: "Rimbewu", zu: "Ubulili" },
        "Select Gender": { ts: "Hlawula Rimbewu", zu: "Khetha Ubulili" },
        "Picture": { ts: "Xifaniso", zu: "Isithombe" },
        "Attach Picture": { ts: "Hlanganisa Xifaniso", zu: "Namathisela Isithombe" },
        "Attach Image": { ts: "Hlanganisa Xifaniso", zu: "Namathisela Isithombe" },
        "Describe the issue...": { ts: "Hlamusela xiphiqo lexi...", zu: "Chaza inkinga lapha..." },
        "Submit Report": { ts: "Rhumela Xiviko", zu: "Thumela Umbiko" },
        "Submit Issue": { ts: "Rhumela Xiphiqo", zu: "Thumela Inkinga" },
        "Submitting...": { ts: "Swa rhumeriwa...", zu: "Kuyathunyelwa..." },
        "Found something that needs fixing? Let us know and we'll handle it.": { ts: "Xana ku na xiphiqo lexi lavaka ku lunghisiwa? Hi byele hi ta xi lulamisa.", zu: "Kukhona okonakele okudinga ukulungiswa? Sazise sizokusiza ngokushesha." },
        "Fill out the form below to report a maintenance problem in your room or common area.": { ts: "Tata fomo leyi nga laha hansi ku bika xiphiqo ekamareni kumbe endhawini ya hinkwavo.", zu: "Gcwalisa ifomu elingezansi ukubika inkinga ekamelweni lakho noma endaweni evamile." },

        // Profile Page
        "Personal Information": { ts: "Vuxokoxoko bya Wena", zu: "Imininingwane Yakho" },
        "Full Name": { ts: "Vito Hinkwaro", zu: "Igama Eliphelele" },
        "Email Address": { ts: "Imeyili", zu: "Ikheli Le-imeyili" },
        "Email address": { ts: "Imeyili", zu: "Ikheli Le-imeyili" },
        "Phone Number": { ts: "Nomboro ya Riqingo", zu: "Inombolo Yocingo" },
        "Student Number": { ts: "Nomboro ya Mudyondzi", zu: "Inombolo Yomfundi" },
        "Staff ID": { ts: "ID ya Mutirhi", zu: "I-ID Yomsebenzi" },
        "Department": { ts: "Ndzawulo", zu: "Umnyango" },
        "Residence Details": { ts: "Vuxokoxoko bya Residense", zu: "Imininingwane Yendawo Yokuhlala" },
        "Current Residence": { ts: "Residense ya Sweswi", zu: "Indawo Yokuhlala Yamanje" },
        "Update Profile": { ts: "Pfuxeta Phurofayili", zu: "Buyekeza Iphrofayili" },

        // Auth & Login
        "Student Login": { ts: "Mudyondzi - Nghena", zu: "Umfundi - Ngena" },
        "Staff Login": { ts: "Mutirhi - Nghena", zu: "Umsebenzi - Ngena" },
        "Admin Login": { ts: "Mulanguteri - Nghena", zu: "Umlawuli - Ngena" },
        "Sign In to Your Account": { ts: "Nghena eka Akhawunti ya Wena", zu: "Ngena Ku-akhawunti Yakho" },
        "Create an Account": { ts: "Endla Akhawunti Yintshwa", zu: "Yenza I-akhawunti Entsha" },
        "Create Account": { ts: "Endla Akhawunti", zu: "Yenza I-akhawunti" },
        "Create your account": { ts: "Endla akhawunti ya wena", zu: "Yenza i-akhawunti yakho" },
        "Password": { ts: "Phasiwedi", zu: "Iphasiwedi" },
        "Enter your password": { ts: "Nghenisa phasiwedi ya wena", zu: "Faka iphasiwedi yakho" },
        "Remember me": { ts: "Ndzi tsundzuke", zu: "Ngikhumbule" },
        "Forgot Password?": { ts: "U rivale phasiwedi?", zu: "Ukhohlwe iphasiwedi?" },
        "Don't have an account?": { ts: "A wu na akhawunti?", zu: "Awunayo i-akhawunti?" },
        "Already have an account?": { ts: "U na yona akhawunti?", zu: "Usunayo i-akhawunti?" },
        "New to MainRes?": { ts: "U muntshwa eka MainRes?", zu: "Umusha ku-MainRes?" },
        "Select your role": { ts: "Hlawula xiyimo xa wena", zu: "Khetha indima yakho" },
        "I am a...": { ts: "Ndzi...", zu: "Ngingu..." },
        "Account type": { ts: "Muxaka wa akhawunti", zu: "Uhlobo lwe-akhawunti" },
        "Log in to report and manage your maintenance requests.": { ts: "Nghena ku bika no lawula swikombelo swa wena swo lunghisa.", zu: "Ngena ukuze ubike uphinde uphathe izicelo zakho zokulungisa." },
        "Set up your account to get maintenance support.": { ts: "Hlela akhawunti ya wena ku kuma mpfuno wo lunghisa.", zu: "Setha i-akhawunti yakho ukuze uthole usizo lokulungisa." },

        // Home Page & Public
        "Premium maintenance services at your fingertips": { ts: "Vukorhokeri bya nkululeko lebyinene hi ku hatlisa", zu: "Izinsiza ezisezingeni eliphezulu zokulungisa ezandleni zakho" },
        "Report and manage your residence maintenance requests easily and efficiently.": { ts: "Bika no fambisa swikombelo swa wena swo lunghisa hi ku olova no hatlisa.", zu: "Bika uphinde uphathe izicelo zakho zokulungisa kalula nangokushesha." },
        "Report an Issue Now": { ts: "Bika Xiphiqo Sweswi", zu: "Bika Inkinga Manje" },
        "Log In to Portal": { ts: "Nghena eka Phothali", zu: "Ngena Ephasini" },
        "Why Choose Us?": { ts: "Hikwalaho ka Yini u Hlawula Hina?", zu: "Kungani Ukhetha Thina?" },
        "Fast Response": { ts: "Nhlamulo ya Xihatla", zu: "Impendulo Esheshayo" },
        "Real-Time Tracking": { ts: "Ku Landelerisa ka Nkarhi wa Xiviri", zu: "Ukulandelela Kwesikhathi Sangempela" },
        "Expert Staff": { ts: "Vatshila lava Dyondzekeke", zu: "Abasebenzi Abangochwepheshe" },
        "Easy Reporting": { ts: "Ku Bika ko Olova", zu: "Ukubika Okulula" },

        // Admin & Staff Management
        "Total Students": { ts: "Vadyondzi Hinkwavo", zu: "Abafundi Bonke" },
        "Total Staff": { ts: "Vatirhi Hinkwavo", zu: "Abasebenzi Bonke" },
        "Add Student": { ts: "Engetela Mudyondzi", zu: "Faka Umfundi" },
        "Add Staff Member": { ts: "Engetela Mutirhi", zu: "Faka Umsebenzi" },
        "Assign Staff": { ts: "Nyika Mutirhi", zu: "Yabela Umsebenzi" },
        "Update Status": { ts: "Pfuxeta Xiyimo", zu: "Buyekeza Isimo" },
        "Change Status": { ts: "Cinca Xiyimo", zu: "Shintsha Isimo" },
        "Work Notes": { ts: "Matsalwa ya Ntirho", zu: "Amanothi Omsebenzi" },
        "Add Note": { ts: "Engetela Tsalwa", zu: "Faka Inothi" },
        "Technician Notes": { ts: "Matsalwa ya Mutshila", zu: "Amanothi Ochwepheshe" },
        "Pending Assignment": { ts: "Ku Yimeriwe ku Nyikiwa", zu: "Kusalindwe Ukwabelwa" },
        "Not Assigned": { ts: "A swi si Nyikiwa", zu: "Akukabelwa" },
        "Assigned": { ts: "Swi Nyikiwile", zu: "Kwabelwe" },
        "Closed": { ts: "Swi Pfariwile", zu: "Kuvaliwe" },
        "Open": { ts: "Swi Pfuriwile", zu: "Kuvuliwe" }
    };

    // Build fast normalized dictionary map (lowercased, trimmed)
    const normalizedMap = new Map();
    // Reverse maps for restoring original strings: reverseMap[lang][translatedText] = originalText
    const reverseMap = {
        ts: new Map(),
        zu: new Map()
    };

    Object.keys(DICTIONARY).forEach(enText => {
        const normKey = enText.trim().toLowerCase();
        const entry = DICTIONARY[enText];
        normalizedMap.set(normKey, { original: enText, translations: entry });

        if (entry.ts) {
            reverseMap.ts.set(entry.ts.trim().toLowerCase(), enText);
        }
        if (entry.zu) {
            reverseMap.zu.set(entry.zu.trim().toLowerCase(), enText);
        }
    });

    const I18N = {
        supportedLanguages: ['en', 'ts', 'zu'],
        defaultLanguage: 'en',
        currentLanguage: 'en',
        initialized: false,
        observer: null,

        /**
         * Reset language to English (default) upon logout
         */
        resetToEnglish: function () {
            this.currentLanguage = 'en';
            try {
                localStorage.removeItem('preferred_language');
                localStorage.removeItem('lang');
                localStorage.setItem('preferred_language', 'en');
                localStorage.setItem('lang', 'en');
            } catch (e) {}
            try {
                document.cookie = 'lang=en; path=/; max-age=31536000; SameSite=Lax';
            } catch (e) {}
            if (document.documentElement) {
                document.documentElement.setAttribute('lang', 'en');
            }
            if (document.body) {
                this.applyTranslations(document.body);
                this.syncLanguageDropdowns('en');
            }
            try {
                window.dispatchEvent(new CustomEvent('languageChanged', {
                    detail: { language: 'en', previousLanguage: null }
                }));
            } catch (e) {}
            return 'en';
        },

        /**
         * Retrieve the active language code
         */
        getLanguage: function () {
            // Check if user is logging out
            try {
                const search = window.location.search || '';
                if (search.includes('logout') || search.includes('action=logout')) {
                    this.resetToEnglish();
                    return 'en';
                }
            } catch (e) {}

            // 1. URL parameter check (?lang=ts)
            try {
                const params = new URLSearchParams(window.location.search);
                const urlLang = params.get('lang');
                if (urlLang && this.supportedLanguages.includes(urlLang)) {
                    return urlLang;
                }
            } catch (e) {}

            // 2. Cookie (matches PHP $_COOKIE['lang'])
            try {
                const match = document.cookie.match(/(?:^|;\s*)lang=([^;]*)/);
                if (match && match[1] && this.supportedLanguages.includes(match[1])) {
                    return match[1];
                }
            } catch (e) {}

            // 3. LocalStorage
            try {
                const stored = localStorage.getItem('preferred_language') || localStorage.getItem('lang');
                if (stored && this.supportedLanguages.includes(stored)) {
                    return stored;
                }
            } catch (e) {}

            return this.defaultLanguage;
        },

        /**
         * Set language and immediately apply to DOM
         */
        setLanguage: function (lang, options) {
            const opts = Object.assign({ notify: true, syncDropdown: true }, options);
            if (!this.supportedLanguages.includes(lang)) {
                lang = this.defaultLanguage;
            }

            const prevLang = this.currentLanguage;
            this.currentLanguage = lang;

            // Persist in localStorage
            try {
                localStorage.setItem('preferred_language', lang);
                localStorage.setItem('lang', lang);
            } catch (e) {}

            // Persist in Cookie (available to all PHP endpoints)
            try {
                document.cookie = `lang=${lang}; path=/; max-age=31536000; SameSite=Lax`;
            } catch (e) {}

            // Update <html> lang attribute
            document.documentElement.setAttribute('lang', lang);

            // Apply translations to the document
            this.applyTranslations(document.body);

            // Synchronize any language selects on the page
            if (opts.syncDropdown) {
                this.syncLanguageDropdowns(lang);
            }

            // Dispatch global event
            window.dispatchEvent(new CustomEvent('languageChanged', {
                detail: { language: lang, previousLanguage: prevLang }
            }));

            // Show localized toast notification if requested
            if (opts.notify) {
                this.showLanguageToast(lang);
            }

            return lang;
        },

        /**
         * Look up a translation for a specific string
         */
        t: function (text, targetLang) {
            if (!text || typeof text !== 'string') return text;
            const lang = targetLang || this.currentLanguage || 'en';
            if (lang === 'en') return text;

            const trimmed = text.trim();
            const norm = trimmed.toLowerCase();

            // Direct dictionary match
            if (DICTIONARY[trimmed] && DICTIONARY[trimmed][lang]) {
                return DICTIONARY[trimmed][lang];
            }

            // Normalized lookup
            const normEntry = normalizedMap.get(norm);
            if (normEntry && normEntry.translations && normEntry.translations[lang]) {
                return normEntry.translations[lang];
            }

            // Check if string ends with punctuation like ':' or '.'
            const punctMatch = trimmed.match(/^(.+?)([:.?!]+)$/);
            if (punctMatch) {
                const baseText = punctMatch[1].trim();
                const punct = punctMatch[2];
                const baseEntry = normalizedMap.get(baseText.toLowerCase());
                if (baseEntry && baseEntry.translations && baseEntry.translations[lang]) {
                    return baseEntry.translations[lang] + punct;
                }
            }

            return text;
        },

        /**
         * Resolve the canonical English key from a potentially translated string
         */
        getOriginalText: function (text) {
            if (!text || typeof text !== 'string') return text;
            const trimmed = text.trim();
            const norm = trimmed.toLowerCase();

            if (DICTIONARY[trimmed]) return trimmed;
            if (normalizedMap.has(norm)) return normalizedMap.get(norm).original;

            // Check reverse maps
            if (reverseMap.ts.has(norm)) return reverseMap.ts.get(norm);
            if (reverseMap.zu.has(norm)) return reverseMap.zu.get(norm);

            // Handle punctuation
            const punctMatch = trimmed.match(/^(.+?)([:.?!]+)$/);
            if (punctMatch) {
                const baseNorm = punctMatch[1].trim().toLowerCase();
                const punct = punctMatch[2];
                if (reverseMap.ts.has(baseNorm)) return reverseMap.ts.get(baseNorm) + punct;
                if (reverseMap.zu.has(baseNorm)) return reverseMap.zu.get(baseNorm) + punct;
                if (normalizedMap.has(baseNorm)) return normalizedMap.get(baseNorm).original + punct;
            }

            return text;
        },

        /**
         * Synchronize all language selector <select> elements on the page
         */
        syncLanguageDropdowns: function (lang) {
            const selects = document.querySelectorAll('select');
            selects.forEach(select => {
                const options = Array.from(select.options).map(o => o.value);
                if (options.includes('en') && options.includes('ts') && options.includes('zu')) {
                    if (select.value !== lang) {
                        select.value = lang;
                    }
                }
            });
        },

        /**
         * Show feedback toast when language changes
         */
        showLanguageToast: function (lang) {
            const messages = {
                en: "Language preference saved",
                ts: "Kucinca ka ririmi ku hlayisiwile",
                zu: "Ukukhetha kolimi kulondoloziwe"
            };
            const msg = messages[lang] || messages.en;

            // If page already defines showToast (as in student/settings.php), use it
            if (typeof window.showToast === 'function') {
                try {
                    window.showToast(msg, 'success');
                    return;
                } catch (e) {}
            }

            // Fallback standalone toast
            const existing = document.querySelector('.i18n-toast');
            if (existing) existing.remove();

            const toast = document.createElement('div');
            toast.className = 'i18n-toast';
            toast.style.cssText = `
                position: fixed;
                bottom: 24px;
                right: 24px;
                background: #1e293b;
                color: #22c55e;
                border: 1px solid rgba(34, 197, 94, 0.3);
                padding: 12px 20px;
                border-radius: 8px;
                font-family: 'Inter', system-ui, sans-serif;
                font-size: 14px;
                font-weight: 600;
                display: flex;
                align-items: center;
                gap: 10px;
                box-shadow: 0 10px 25px rgba(0,0,0,0.4);
                z-index: 999999;
                transition: opacity 0.3s ease, transform 0.3s ease;
            `;
            toast.innerHTML = `<i class="fas fa-circle-check" style="color: #22c55e;"></i> <span>${msg}</span>`;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(10px)';
                setTimeout(() => toast.remove(), 300);
            }, 2600);
        },

        /**
         * Walk the DOM and apply translations
         */
        applyTranslations: function (root) {
            const target = root || document.body;
            if (!target) return;

            const lang = this.currentLanguage;

            // 1. Process elements with explicit [data-i18n]
            const explicitEls = target.querySelectorAll('[data-i18n]');
            explicitEls.forEach(el => {
                const key = el.getAttribute('data-i18n');
                if (key) {
                    el.textContent = this.t(key, lang);
                }
            });

            // 2. Process form inputs and placeholders
            const inputs = target.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                if (input.placeholder) {
                    if (!input._i18nOrigPlaceholder) {
                        input._i18nOrigPlaceholder = this.getOriginalText(input.placeholder);
                    }
                    if (lang === 'en') {
                        input.placeholder = input._i18nOrigPlaceholder;
                    } else {
                        const translated = this.t(input._i18nOrigPlaceholder, lang);
                        if (translated && translated !== input._i18nOrigPlaceholder) {
                            input.placeholder = translated;
                        }
                    }
                }
                if (input.title) {
                    if (!input._i18nOrigTitle) {
                        input._i18nOrigTitle = this.getOriginalText(input.title);
                    }
                    if (lang === 'en') {
                        input.title = input._i18nOrigTitle;
                    } else {
                        const translated = this.t(input._i18nOrigTitle, lang);
                        if (translated) input.title = translated;
                    }
                }
                if (input.type === 'submit' || input.type === 'button') {
                    if (input.value) {
                        if (!input._i18nOrigValue) {
                            input._i18nOrigValue = this.getOriginalText(input.value);
                        }
                        if (lang === 'en') {
                            input.value = input._i18nOrigValue;
                        } else {
                            const translated = this.t(input._i18nOrigValue, lang);
                            if (translated) input.value = translated;
                        }
                    }
                }
            });

            // 3. Process Select Options (Skip the language selector itself)
            const selects = target.querySelectorAll('select');
            selects.forEach(select => {
                const optValues = Array.from(select.options).map(o => o.value);
                const isLanguageSelector = optValues.includes('en') && optValues.includes('ts') && optValues.includes('zu');
                if (isLanguageSelector) {
                    return; // Preserve the language picker options
                }

                Array.from(select.options).forEach(opt => {
                    if (!opt._i18nOrigText) {
                        opt._i18nOrigText = this.getOriginalText(opt.textContent);
                    }
                    if (lang === 'en') {
                        opt.textContent = opt._i18nOrigText;
                    } else {
                        const translated = this.t(opt._i18nOrigText, lang);
                        if (translated && translated !== opt._i18nOrigText) {
                            opt.textContent = translated;
                        }
                    }
                });
            });

            // 4. Walk Text Nodes across the DOM
            if (!document.createTreeWalker || typeof NodeFilter === 'undefined') {
                return;
            }

            const walker = document.createTreeWalker(
                target,
                NodeFilter.SHOW_TEXT,
                {
                    acceptNode: function (node) {
                        if (!node.nodeValue || !node.nodeValue.trim()) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        const parent = node.parentElement;
                        if (!parent) return NodeFilter.FILTER_REJECT;

                        const tag = parent.tagName;
                        // Avoid translating scripts, styles, preformatted code, svg, and language selects
                        if (['SCRIPT', 'STYLE', 'CODE', 'PRE', 'NOSCRIPT', 'SVG', 'TEXTAREA'].includes(tag)) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        if (parent.closest('select') && Array.from(parent.closest('select').options).some(o => o.value === 'ts')) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        if (parent.hasAttribute('data-i18n-ignore') || parent.classList.contains('no-translate')) {
                            return NodeFilter.FILTER_REJECT;
                        }
                        return NodeFilter.FILTER_ACCEPT;
                    }
                },
                false
            );

            const nodesToTranslate = [];
            let currentNode = walker.nextNode();
            while (currentNode) {
                nodesToTranslate.push(currentNode);
                currentNode = walker.nextNode();
            }

            nodesToTranslate.forEach(node => {
                const currentVal = node.nodeValue;
                const trimmed = currentVal.trim();
                if (!trimmed) return;

                // Cache original English text on the node if not already cached
                if (!node._i18nOriginal) {
                    node._i18nOriginal = this.getOriginalText(trimmed);
                }

                // Lead/trail whitespace preservation
                const leadMatch = currentVal.match(/^\s*/);
                const trailMatch = currentVal.match(/\s*$/);
                const leadSpace = leadMatch ? leadMatch[0] : '';
                const trailSpace = trailMatch ? trailMatch[0] : '';

                if (lang === 'en') {
                    if (node._i18nOriginal) {
                        node.nodeValue = leadSpace + node._i18nOriginal + trailSpace;
                    }
                } else {
                    const translated = this.t(node._i18nOriginal, lang);
                    if (translated && translated !== node._i18nOriginal) {
                        node.nodeValue = leadSpace + translated + trailSpace;
                    }
                }
            });
        },

        /**
         * Initialize the engine on page load
         */
        init: function () {
            if (this.initialized) return;
            this.initialized = true;

            this.currentLanguage = this.getLanguage();

            // Set document lang attribute
            document.documentElement.setAttribute('lang', this.currentLanguage);

            // Synchronize dropdowns on page load
            this.syncLanguageDropdowns(this.currentLanguage);

            // Apply translations if language is not English
            if (this.currentLanguage !== 'en') {
                this.applyTranslations(document.body);
            }

            // Listen for any logout clicks across buttons and links to reset language immediately
            document.addEventListener('click', (e) => {
                const el = e.target.closest('a, button');
                if (!el) return;
                const href = (el.getAttribute('href') || '').toLowerCase();
                const onclick = (el.getAttribute('onclick') || '').toLowerCase();
                const text = (el.textContent || '').trim().toLowerCase();
                if (
                    href.includes('action=logout') ||
                    href.includes('logout') ||
                    onclick.includes('action=logout') ||
                    onclick.includes('logout') ||
                    el.id === 'nav-logout' ||
                    el.classList.contains('logout-link') ||
                    text === 'log out' ||
                    text === 'sign out' ||
                    text === 'logout' ||
                    text === 'huma' ||
                    text === 'phuma'
                ) {
                    this.resetToEnglish();
                }
            }, true);

            // Hook into any language selectors on the page
            document.addEventListener('change', (e) => {
                const target = e.target;
                if (target && target.tagName === 'SELECT') {
                    const optVals = Array.from(target.options).map(o => o.value);
                    if (optVals.includes('en') && optVals.includes('ts') && optVals.includes('zu')) {
                        if (target.value && this.supportedLanguages.includes(target.value)) {
                            this.setLanguage(target.value);
                        }
                    }
                }
            });

            // Set up MutationObserver to translate dynamically rendered elements (e.g. issues, modals)
            if (typeof MutationObserver !== 'undefined' && document.body) {
                let debounceTimer = null;
                this.observer = new MutationObserver((mutations) => {
                    if (this.currentLanguage === 'en') return;

                    let shouldTranslate = false;
                    for (const m of mutations) {
                        if (m.type === 'childList' && m.addedNodes.length > 0) {
                            for (const node of m.addedNodes) {
                                if (node.nodeType === 1 && !node.classList?.contains('i18n-toast') && !node.classList?.contains('toast')) {
                                    shouldTranslate = true;
                                    break;
                                }
                            }
                        }
                        if (shouldTranslate) break;
                    }

                    if (shouldTranslate) {
                        clearTimeout(debounceTimer);
                        debounceTimer = setTimeout(() => {
                            this.applyTranslations(document.body);
                        }, 50);
                    }
                });

                this.observer.observe(document.body, { childList: true, subtree: true });
            }
        }
    };

    // Global bridge for inline onchange="changeLanguage(this.value)"
    window.changeLanguage = function (lang) {
        return I18N.setLanguage(lang);
    };

    // Public API
    window.I18N = I18N;
    window.resetLanguage = function () {
        return I18N.resetToEnglish();
    };
    window.t = function (key, lang) {
        return I18N.t(key, lang);
    };

    // Auto-initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => I18N.init());
    } else {
        I18N.init();
    }

})(window, document);
