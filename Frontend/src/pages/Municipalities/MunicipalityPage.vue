<template>
  <div class="flex">
    <!-- <Sidebar /> -->
    <main class="flex-1 overflow-y-auto  bg-gray-50">
      <!-- <DashboardLayout /> -->

      <div class="space-y-6 p-6">

        <!-- ── Loading Skeleton ─────────────────────────── -->
        <template v-if="listLoading && !municipality">
          <Card class="animate-pulse">
            <div class="h-16 bg-gray-200" />
            <CardContent class="flex items-center gap-6">
              <div class="w-20 h-20 rounded-xl bg-gray-200 shrink-0" />
              <div class="space-y-2 flex-1">
                <div class="h-5 bg-gray-200 rounded w-2/3" />
                <div class="h-4 bg-gray-200 rounded w-1/2" />
                <div class="h-4 bg-gray-200 rounded w-1/3" />
              </div>
            </CardContent>
          </Card>

          <Card class="animate-pulse">
            <div class="h-16 bg-gray-200" />
            <CardContent class="space-y-4">
              <div v-for="i in 4" :key="i" class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-gray-200 shrink-0" />
                <div class="space-y-1.5 flex-1">
                  <div class="h-3 bg-gray-200 rounded w-1/4" />
                  <div class="h-4 bg-gray-200 rounded w-1/2" />
                </div>
              </div>
            </CardContent>
          </Card>
        </template>

        <!-- ── Error State ──────────────────────────────── -->
        <div
          v-else-if="error"
          class="rounded-lg border border-red-200 bg-red-50 text-red-600 px-5 py-4 text-sm flex items-center gap-2"
        >
          <AlertCircle :size="16" />
          {{ error }}
        </div>

        <!-- ── Data Loaded ───────────────────────────────── -->
        <template v-else-if="municipality">

          <!-- Success Toast -->
          <Transition name="fade">
            <div
              v-if="saved"
              class="rounded-lg border border-emerald-300 bg-emerald-50 text-emerald-700 px-5 py-3 text-sm flex items-center gap-2"
            >
              <CheckCircle :size="16" />
              Municipalité mise à jour avec succès !
            </div>
          </Transition>

          <!-- ── Profile Card ──────────────────────────── -->
          <Card>
            <CardHeader :grid="true">
              <h2 class="text-white text-lg mb-1">Profil Municipalité</h2>
              <p class="text-white/50 text-sm">Informations sur votre collectivité</p>
            </CardHeader>

            <CardContent class="p-8">
              <div class="flex flex-col sm:flex-row items-start gap-6 pb-8 border-b border-gray-100">
                <!-- Building Icon -->
                <div class="w-20 h-20 rounded-xl bg-gradient-to-br from-[#0F2356] to-[#162d63] flex items-center justify-center shrink-0 shadow-lg">
                  <Building2 class="w-10 h-10 text-[#CC1525]" />
                </div>

                <!-- Info + Actions -->
                <div class="flex-1">
                  <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-4">
                    <div>
                      <h3 class="text-2xl text-[#0F2356] mb-1">{{ municipality.name }}</h3>
                      <div class="flex items-center gap-2 text-gray-500 text-sm">
                        <MapPin class="w-4 h-4 text-[#CC1525]" />
                        <span>{{ municipality.address }}</span>
                      </div>
                      <!-- FIX: afficher gouvernorat · ville via la relation FK -->
                      <p class="text-gray-500 text-sm mt-1">
                        {{ municipality.city?.governorate?.name ?? '' }}{{ municipality.city?.name ? ' · ' + municipality.city.name : '' }}
                      </p>
                    </div>

                    <!-- Edit / Save / Cancel -->
                    <div class="flex items-center gap-2 sm:ml-auto shrink-0">
                      <Button v-if="!editing" class="bg-[#CC1525] text-white" variant="outline" size="sm" @click="startEditing">
                        <Pencil :size="14" />
                        Modifier
                      </Button>
                      <template v-else>
                        <Button variant="outline" size="sm" @click="cancelEditing">
                          Annuler
                        </Button>
                        <Button
                          variant="default"
                          size="sm"
                          :disabled="actionLoading"
                          @click="handleSave"
                        >
                          <Loader2 v-if="actionLoading" :size="14" class="animate-spin" />
                          <Save v-else :size="14" />
                          Enregistrer
                        </Button>
                      </template>
                    </div>
                  </div>

                  <!-- Stats Grid -->
                  <div class="grid grid-cols-3 gap-4">
                    <div class="bg-[#F7F8FB] border border-gray-200 rounded-lg p-4">
                      <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Population</p>
                      <p class="text-2xl text-[#0F2356]">{{ municipality.number_of_inhabitants ?? '—' }}</p>
                    </div>
                    <div class="bg-[#F7F8FB] border border-gray-200 rounded-lg p-4">
                      <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Superficie</p>
                      <p class="text-2xl text-[#0F2356]">{{ municipality.surface ?? '—' }}</p>
                    </div>
                    <div class="bg-[#F7F8FB] border border-gray-200 rounded-lg p-4">
                      <p class="text-gray-500 text-xs uppercase tracking-wide mb-1">Gouvernorat</p>
                      <!-- FIX: municipality.governorate (string supprimé) → relation city.governorate.name -->
                      <p class="text-xl text-[#0F2356]">{{ municipality.city?.governorate?.name ?? '—' }}</p>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- ── Contact Card ──────────────────────────── -->
          <Card>
            <CardHeader variant="gradient" :accent="true">
              <h3 class="text-white text-lg mb-1">Coordonnées</h3>
              <p class="text-white/50 text-sm">Informations de contact de la mairie</p>
            </CardHeader>

            <CardContent class="p-8">
              <!-- Contact Fields Grid -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                  v-for="item in editableFields"
                  :key="item.key"
                  class="flex items-start gap-4"
                >
                  <div class="w-12 h-12 rounded-lg bg-[#F7F8FB] border border-gray-200 flex items-center justify-center shrink-0">
                    <component :is="item.icon" class="w-5 h-5 text-[#CC1525]" />
                  </div>
                  <div class="flex-1">
                    <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">{{ item.label }}</p>
                    <!-- Edit mode -->
                    <input
                      v-if="editing"
                      v-model="form[item.key]"
                      :type="item.type ?? 'text'"
                      class="w-full px-3 py-1.5 rounded-lg border border-gray-200 bg-[#F7F8FB] text-[#0F2356] text-sm focus:outline-none focus:border-[#0F2356] focus:ring-2 focus:ring-[#0F2356]/20 transition-colors"
                    />
                    <!-- Read mode -->
                    <p v-else class="text-[#0F2356] text-lg">
                      {{ municipality[item.key] ?? '—' }}
                    </p>
                  </div>
                </div>
              </div>

              <!-- FIX: Pays — lecture seule via relation, plus de champ 'country' en DB -->
              <div class="mt-6 flex items-start gap-4">
                <div class="w-12 h-12 rounded-lg bg-[#F7F8FB] border border-gray-200 flex items-center justify-center shrink-0">
                  <Globe class="w-5 h-5 text-[#CC1525]" />
                </div>
                <div class="flex-1">
                  <p class="text-xs text-gray-500 uppercase tracking-wide mb-1">Pays</p>
                  <p class="text-[#0F2356] text-lg">
                    {{ municipality.city?.governorate?.country?.name ?? '—' }}
                  </p>
                </div>
              </div>

              <!-- Administrative Info -->
              <div class="mt-8 pt-8 border-t border-gray-100">
                <div class="bg-[#F7F8FB] border-l-4 border-[#CC1525] rounded-lg p-6">
                  <h4 class="text-[#0F2356] font-medium mb-3 flex items-center gap-2">
                    <Building2 class="w-5 h-5 text-[#CC1525]" />
                    Informations Administratives
                  </h4>
                  <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                      <p class="text-gray-500 mb-1">Code Postal</p>
                      <p class="text-[#0F2356]">{{ municipality.postal_code ?? '—' }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 mb-1">Délégation</p>
                      <p class="text-[#0F2356]">{{ municipality.delegation ?? '—' }}</p>
                    </div>
                    <div>
                      <p class="text-gray-500 mb-1">Nombre de Zones</p>
                      <p class="text-[#0F2356]">
                        {{ municipality.zones_count ? `${municipality.zones_count} zones urbaines` : '—' }}
                      </p>
                    </div>
                    <div>
                      <p class="text-gray-500 mb-1">Agents Actifs</p>
                      <p class="text-[#0F2356]">
                        {{ municipality.agents_count ? `${municipality.agents_count} agents` : '—' }}
                      </p>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

        </template>
      </div>
    </main>
  </div>
</template>

<script setup>
import { onMounted, ref, reactive } from "vue";
import {
  Building2, MapPin, Phone, Mail, Globe,
  AlertCircle, Pencil, Save, Loader2, CheckCircle,
  Calendar, ChevronDown, Filter, Download,
} from "lucide-vue-next";

// ── Layout ────────────────────────────────────────────
import Sidebar        from "../../components/Sidebar/Sidebar.vue";
import DashboardLayout from "../../components/DashboardLayout.vue";

// ── UI Components ─────────────────────────────────────
import Card        from "../../components/ui/Card.vue";
import CardHeader  from "../../components/ui/CardHeader.vue";
import CardContent from "../../components/ui/CardContent.vue";
import Button      from "../../components/Button.vue";

// ── Composable ────────────────────────────────────────
import { useMunicipalityStore } from "../../stores/MunicipalityStore";
import { storeToRefs } from "pinia";

const municipalityStore = useMunicipalityStore();
const { municipality, loading: listLoading, actionLoading, error } = storeToRefs(municipalityStore);
const { fetchMyMunicipality, updateMyMunicipality } = municipalityStore;

onMounted(fetchMyMunicipality);

// ── Edit State ────────────────────────────────────────
const editing = ref(false);
const saved   = ref(false);
const form    = reactive({});

// FIX: 'country' retiré — n'est plus une colonne de municipalities
//      Le pays est affiché en lecture seule via city.governorate.country
const editableFields = [
  { key: "phone",   icon: Phone,  label: "Numéro de téléphone" },
  { key: "email",   icon: Mail,   label: "Email",  type: "email" },
  { key: "address", icon: MapPin, label: "Adresse" },
];

function startEditing() {
  editableFields.forEach((f) => {
    form[f.key] = municipality.value?.[f.key] ?? "";
  });
  editing.value = true;
}

function cancelEditing() {
  editing.value = false;
}

async function handleSave() {
  await updateMyMunicipality(form);
  if (!error.value) {
    editing.value = false;
    saved.value   = true;
    setTimeout(() => (saved.value = false), 3000);
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease, transform 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
  transform: translateY(-6px);
}
</style>