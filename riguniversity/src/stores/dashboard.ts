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

export interface HomeWorkList {
    homework_id: string
    title: string
    due_date: string
    submitted: string
}


export const useDashboardStore = defineStore('dashboard', () => {
    const toast = useToast()
    const enrolledPrograms = ref<any[]>([])
    const news = ref<News[]>([])
    const homeworkList = ref<HomeWorkList[]>([])
    const filter = reactive({
      search: ''
    })
    const loading = reactive({
      enrolledPrograms: false,
        news: false,
        homework: false,
    })
    const studentId = computed(() => {
        const studentFirstName = skwp_ajax_object.userInfo.user_name;
        return studentFirstName
      })

      const fetchEnrolledPrograms = () => {
        loading.enrolledPrograms = true
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
                loading.enrolledPrograms = false
            })
        }

        const fetchNews = () => {
            loading.news = true;
    
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
                        // console.log(response.data)
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
                    loading.news = false;
                });
        };

        const fetchHomework = async () => {
            loading.homework = true;
            try {
              const response = await fetch(skwp_ajax_object.ajaxurl,
                {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'run_list_student_homework',
                }),
              });
              const data = await response.json();
              
              if (response.ok) {
                homeworkList.value = data.data;
                toast.add({
                  severity: 'success',
                  summary: 'Success',
                  detail: 'Homework fetched successfully',
                  life: 3000,
                });
              } else {
                throw new Error(data.message || 'Failed to fetch homework');
              }
            } catch (error) {
              toast.add({
                severity: 'error',
                summary: 'Error',
                detail: error.message,
                life: 3000,
              });
            } finally {
              loading.homework = false;
            }
          };

        // watch(filter, () => {
        //     fetchEnrolledPrograms();
        //     fetchNews();
        //     fetchHomework();
        // });

        // Watch for changes in filter and trigger fetches
        watch(filter, () => {
            Promise.all([fetchEnrolledPrograms(), fetchNews(), fetchHomework()])
            .catch((error) => console.error('Error fetching data:', error))
        }, { deep: true }) 
  


    return { 
        enrolledPrograms: computed(() => enrolledPrograms),
        news: computed(() => news),
        fetchHomework: computed(() => fetchHomework),
        homeworkList: computed(() => homeworkList),
        fetchEnrolledPrograms: fetchEnrolledPrograms,
        fetchNews: fetchNews,
        filter: computed(() => filter),
        loading: computed(() => loading),
        studentId,
    }
})