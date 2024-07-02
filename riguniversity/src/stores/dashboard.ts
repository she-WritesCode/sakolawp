import { ref, computed, watch, reactive } from 'vue'
import { defineStore } from 'pinia'
import { useToast } from 'primevue/usetoast'

export interface News {
    ID: Number
    img_url: string
    title: string
    content: string
    permalink: string
    date: Date
    category_terms: any
}


export const useDashboardStore = defineStore('dashboard', () => {
    const toast = useToast()
    const enrolledPrograms = ref<any[]>([])
    const news = ref<News[]>([])
    const filter = reactive({
      search: ''
    })
    const loading = reactive({
      list: false,
      eventList: false,
      get: false,
    })
    const studentId = computed(() => {
        const studentFirstName = skwp_ajax_object.userInfo.user_name;
        return studentFirstName
      })

      const fetchEnrolledPrograms = () => {
        loading.list = true
        // @ts-ignore
        fetch(skwp_ajax_object.ajaxurl, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
              action: 'run_user_enrolled_classes',
              ...filter
            })
          })
            .then((response) => response.json())
            .then((response) => {
              //console.log(response)
              enrolledPrograms.value = response.data
              toast.add({
                severity: 'success',
                summary: 'Success',
                detail: 'Programs fetched successfully',
                life: 3000
              })
              
            })
            .catch((error) => {
              console.error('Error:', error)
            })
            .finally(() => {
                loading.list = false
            })
        }

        const fetchNews = () => {
            loading.eventList = true;
    
            fetch(skwp_ajax_object.ajaxurl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: new URLSearchParams({
                    action: 'run_latest_news',
                })
            })
                .then((response) => response.json())
                .then((response) => {
                    if (response.success) {
                        console.log(response.data)
                        news.value = response.data;
                        toast.add({
                            severity: 'success',
                            summary: 'Success',
                            detail: 'Events fetched successfully',
                            life: 3000
                        });
                    } else {
                        toast.add({
                            severity: 'error',
                            summary: 'Error',
                            detail: 'Failed to fetch events',
                            life: 3000
                        });
                    }
                })
                .catch((error) => {
                    console.error('Error:', error);
                    toast.add({
                        severity: 'error',
                        summary: 'Error',
                        detail: 'Failed to fetch events',
                        life: 3000
                    });
                })
                .finally(() => {
                    loading.eventList = false;
                });
        };

        watch(filter, () => {
            fetchEnrolledPrograms();
            fetchNews();
        });

  


    return { 
        enrolledPrograms: computed(() => enrolledPrograms),
        news: computed(() => news),
        fetchEnrolledPrograms: fetchEnrolledPrograms,
        fetchNews: fetchNews,
        filter: computed(() => filter),
        loading: computed(() => loading),
        studentId,
    }
})